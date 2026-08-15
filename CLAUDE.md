# Nami — Claude Code 向けプロジェクトガイド

## プロジェクト概要

BSC（バランスト・スコアカード）に基づく、組織向けKPI管理アプリ。

- 顧客企業が戦略目標を4視点（財務/顧客/業務プロセス/学習と成長）で管理し、KPIに落とし込み、月次で実績を入力・振り返る
- **商用SaaS**。複数の顧客企業（テナント）が同一システムを利用する
- 差別化は「メンバーが入力を続けられるUX」。機能の多さでは競わない

---

## 🚨 絶対に守るルール

### 1. マルチテナント分離

**A社のユーザーがB社のデータを閲覧・操作できる状態は、いかなる理由でも許容されない。**

- テナントに属する全モデルは `company_id` を **直接** 持つ（親テーブル経由で辿らせない。意図的な非正規化）
- `company_id` は常に `NOT NULL`。nullable にしない
- 全テナントモデルに Laravel の **Global Scope を適用** し、`company_id` で自動的に絞る
- **クエリに `if (運営者) { 全件取得 }` のような分岐を書かない。** 越境するクエリはアプリ全体で「顧客企業一覧」の1箇所のみ
- 新しいモデルを追加するときは、必ず Global Scope とテナント分離テストをセットで作る

### 2. 運営者（operator）の扱い

- `operators` テーブルは `users` と**完全に分離**。認証ガードもルートも別
  - 顧客: `/` 配下、`web` ガード
  - 運営者: `/operator/*` 配下、`operator` ガード
- 運営者は「1社を選んで入る」方式。選択結果をセッションの `active_company_id` に保持し、**顧客と同じ Global Scope が適用される**
- `operators.role`
  - `owner` … 全機能 + 監査ログ閲覧
  - `staff` … 全社の閲覧・書き込み可、**監査ログは閲覧不可**
- 運営者による書き込みは `operator_audit_logs` に全件記録する
- ドメイン上の担当者（`owner_user_id` / `user_id`）には **必ず顧客企業の実在ユーザー** を入れる。運営者IDを入れてはいけない
- 運営者モード時は画面上部に「運営者モード：○○社を閲覧中」を常時表示する

### 3. テストは必須

- **全機能に Feature テストを書く。** 実装だけ先に出して「テストは後で」は不可
- 特に以下は必ずテストする
  - **テナント分離**：他社のIDを指定してアクセスした場合に 403/404 になること
  - 運営者ガードに顧客セッションで到達できないこと
  - `staff` ロールが監査ログにアクセスできないこと
- テストが通らないコードはコミットしない

---

## 技術スタック

| レイヤー | 技術 | バージョン |
|---|---|---|
| フロントエンド | Next.js (App Router) + TypeScript | Node.js 20.x |
| UIライブラリ | **shadcn/ui** + Tailwind CSS | — |
| データ取得 | **TanStack Query** | — |
| バックエンド | Laravel | 13.x (PHP 8.4) |
| DB | MySQL 8.0（本番は Aurora Serverless v2） | 8.0 |
| 認証 | **Laravel標準（Breeze / Fortify）** | — |
| コンテナ | Docker Compose（ローカル）/ ECS Fargate（本番） | — |
| IaC | Terraform | AWS Provider 5.x |

**認証について**：Cognito は使わない。ローカル完結で開発速度を優先する方針。商用化後に載せ替えを検討する。

---

## ディレクトリ構成

```
Nami/
├── frontend/          # Next.js
├── backend/           # Laravel
├── infra/             # Terraform（VPC/ALB/ECS Fargate/ECR/IAM）
├── docs/
│   └── database/
│       └── er-diagram.md   # ★スキーマの正。実装前に必ず参照
├── docker-compose.yml
└── CLAUDE.md
```

---

## データモデル

**正式なスキーマ定義は `docs/database/er-diagram.md` を参照すること。** 以下は要点のみ。

### テーブル一覧

| テーブル | 役割 |
|---|---|
| `operators` | サービス運営者（owner / staff） |
| `operator_audit_logs` | 運営者の操作記録 |
| `companies` | 顧客企業（テナント） |
| `departments` | 部署 |
| `users` | 顧客企業のユーザー |
| `fiscal_years` | 年度（開始月・締め日を保持） |
| `strategy_goals` | 戦略目標 |
| `kpis` | KPI |
| `kpi_targets` | 目標値（期間ごと） |
| `kpi_records` | 実績値（期間ごと） |
| `action_plans` | 行動計画・振り返り |

### 重要な設計意図

**年度（fiscal_years）**
- 会社ごとに会計年度の開始月・締め日が異なる（4月開始/1月開始、月末締め/20日締め）
- `start_month` + `closing_day` から期間を自動生成する
- 生成後の個別期間は手動編集も可能
- `status`（draft/active/closed）で締め済み年度の編集を制御

**期間の持ち方（kpi_targets / kpi_records）**
- `year_month` のような固定単位は **使わない**
- `period_type` + `period_start` + `period_end`（日付範囲）で保持する
- これにより、月をまたぐ締め日・年度をまたぐ週・四半期・日次に、スキーマ変更なしで対応できる
- **MVPの実装は `period_type = 'month'` のみ。** 日次・週次のUIは作らない（構造だけ用意しておく）
- 同一KPI・同一 `period_type` 内で、期間の重複・欠落がないことをバリデーションで担保する

**KPI**
- `polarity`：`positive`（売上など上昇が good）/ `negative`（コストなど下降が good）。達成判定の向きを自動化する
- `aggregation_type`：`sum`（累計型：売上）/ `average`・`latest`（実測型：顧客満足度）。年間実績の集計方法が異なるため必須
- 年間目標から月次へ **均等按分するボタン** を提供し、月ごとの手動調整も可能にする

---

## コーディング規約

### Laravel

- バリデーションは **FormRequest** に寄せる。コントローラ内に `$request->validate()` を書かない
- ビジネスロジックはコントローラに書かず、Service クラス or モデルに置く
- 削除は原則 **論理削除（SoftDeletes）**。BtoBの監査要件を考慮
- APIレスポンスは **API Resource** で整形する
- N+1 を避ける（`with()` で eager load）

### API 設計

```
GET    /api/strategy-goals          一覧
POST   /api/strategy-goals          作成
GET    /api/strategy-goals/{id}     詳細
PUT    /api/strategy-goals/{id}     更新
DELETE /api/strategy-goals/{id}     削除
```

- URLはケバブケース、複数形
- エラーレスポンスは Laravel 標準形式に統一

### Next.js

- **shadcn/ui のコンポーネントを優先。** 独自実装は既存コンポーネントで賄えない場合のみ
- サーバー状態は **TanStack Query** で管理。`useState` でAPIレスポンスを持たない
- 型は `types/` に集約し、APIレスポンスの型を必ず定義する

### 共通

- 日本語UI（`APP_LOCALE=ja`）
- タイムゾーンは `Asia/Tokyo`
- 金額・数値の表示フォーマットは共通ユーティリティに集約

---

## 開発コマンド

環境の起動:
```bash
docker compose up
```

マイグレーション:
```bash
docker compose exec backend php artisan migrate
```

テスト実行:
```bash
docker compose exec backend php artisan test
```

Laravel のキャッシュクリア:
```bash
docker compose exec backend php artisan route:clear
```

**注意**：Laravel のコマンドは必ず `docker compose exec backend` 経由で実行する。ホスト側で直接 `php artisan` を叩かない。

---

## ロードマップ（MVPは v1.0 まで）

| バージョン | 内容 |
|---|---|
| v0.1–0.2 | ローカル土台 → クラウド最小公開 → DB接続（**完了**） |
| v0.3 | 認証（Laravel標準ログイン） |
| v0.4 | 戦略目標の管理 |
| v0.5 | KPI管理（戦略目標への紐づけ） |
| v0.6 | 当月実績入力（polarity に基づく達成判定） |
| v0.7 | 行動計画＋振り返り（自己評価・証憑アップロード） |
| v0.8 | マイダッシュボード |
| **v1.0** | **UI磨き込み → 商用リリース** |

v1.5以降（戦略マップGUI、ロール制御、ゲーミフィケーション）は MVP スコープ外。

---

## ❌ やってはいけないこと

- `company_id` の絞り込みを省略したクエリを書く
- Global Scope をバイパスする（`withoutGlobalScope()` の使用は原則禁止。使う場合は理由をコメントで明記し、レビューを求める）
- `users` テーブルに運営者を混ぜる
- `company_id` を nullable にする
- テストを書かずに機能を実装する
- `year_month` のような固定粒度のカラムを追加する
- Cognito 関連のコードを追加する（今回のスコープ外）
- 日次・週次の入力UIを実装する（構造は対応済みだが、MVPスコープ外）
- セキュリティグループの `description` に日本語を使う（AWS APIはASCIIのみ）

---

## 作業の進め方

### 基本方針：速度優先

完璧を目指すより、動くものを早く出してフィードバックを回すことを優先する。
「後で直せばいい」で問題ないものは、確認を求めずに進めてよい。

### ✅ 確認せずに進めてよいこと

- 実装の細部（命名、ファイル分割、内部設計）
- テストコードで検証できる範囲の動作確認
- 読み取り専用のコマンド（`git status`、`docker compose ps`、`php artisan route:list` など）
- リファクタリング、コメント追加、型定義の整備
- 軽微なバグ修正

### 🛑 必ず確認を取ること

**1. 方針が分かれる判断**
- スキーマ変更（`docs/database/er-diagram.md` の更新が必要なもの）
- 技術選定（新しいライブラリの導入、既存構成の変更）
- CLAUDE.md のルールに例外を作る必要が出たとき
- 仕様が曖昧で、解釈によって作るものが変わるとき

**2. 破壊的な操作**

以下は**実行前に必ず確認を取る。恒久許可を求めない**。

- `php artisan migrate:fresh` / `migrate:refresh` / `migrate:rollback`
- データベースの削除・truncate
- `rm -rf`、ファイルの一括削除
- `git reset --hard`、`git push --force`
- `terraform destroy` / `terraform apply`
- 本番環境・AWS環境に影響する操作

**理由**：速く作って直すスタイルは「壊れても直せる」ことが前提。
データの消失は直せないため、ここだけは例外とする。

**3. 人間の目でしか確認できないこと**

以下はテストコードで代替できないため、実装後に手順を提示して確認を依頼する。

- 画面の見た目・レイアウト・使い勝手
- ブラウザ上の実際の操作フロー（ログイン、フォーム送信など）
- Cookie / CORS / セッションのクロスオリジン挙動
- AWS環境での実際の動作

### 完了報告のフォーマット

1機能が完了したら、以下の形式で簡潔に報告する。

```
## 完了：<機能名>

### やったこと
（3〜5行で要約）

### テスト結果
（php artisan test の結果。件数のみでよい）

### 手動で確認してほしいこと
（ブラウザ操作など、テストで代替できないもののみ）
なければ「なし」と書く

### 判断が必要な点
（設計上の判断で確認したいことがあれば。なければ「なし」）
```

**手動確認が「なし」なら、報告だけして次の機能に進んでよい。**
確認待ちで止まる必要はない。

### その他

- テストは実装と同じタイミングで書く（先でも後でもよい）
- 判断に迷ったら質問する。ただし迷わない範囲は勝手に進めてよい