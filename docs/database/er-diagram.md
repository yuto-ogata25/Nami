# Nami ER図

BSC型KPI管理アプリ Nami のデータベース設計。

## 全体構造

```mermaid
erDiagram
    operator ||--o{ operator_audit_log : ""
    company ||--o{ operator_audit_log : ""
    company ||--o{ department : ""
    company ||--o{ user : ""
    company ||--o{ fiscal_year : ""
    fiscal_year ||--o{ strategy_goal : ""
    strategy_goal ||--o{ kpi : ""
    kpi ||--o{ kpi_target : ""
    kpi ||--o{ kpi_record : ""
    strategy_goal ||--o{ action_plan : ""

    operator {
        id id
        string name
        string email
        string password
        string role "owner / staff"
    }
    operator_audit_log {
        id id
        id operator_id
        id company_id
        string action
        string target_type
        id target_id
        datetime created_at
    }
    company {
        id id
        string name
    }
    department {
        id id
        id company_id
        string name
    }
    user {
        id id
        id company_id
        id department_id
        string name
        string email
        string password
        string role
    }
    fiscal_year {
        id id
        id company_id
        int year
        int start_month "1-12"
        int closing_day "1-31"
        string status "draft / active / closed"
    }
    strategy_goal {
        id id
        id company_id
        id fiscal_year_id
        id department_id
        string perspective "4視点"
        string title
        text definition
        int importance
        id owner_user_id
        boolean is_adopted "採用フラグ。一覧でチェックボックス表示"
    }
    kpi {
        id id
        id company_id
        id strategy_goal_id
        string name
        id owner_user_id
        string unit
        string polarity "positive / negative"
        string aggregation_type "sum / average / latest"
        string measurement_cycle "monthly / weekly / daily"
        text note
    }
    kpi_target {
        id id
        id company_id
        id kpi_id
        string period_type "year/quarter/month/week/day/custom"
        date period_start
        date period_end
        decimal target_value
    }
    kpi_record {
        id id
        id company_id
        id kpi_id
        string period_type
        date period_start
        date period_end
        decimal actual_value
    }
    action_plan {
        id id
        id company_id
        id strategy_goal_id
        id user_id
        date period_start
        date period_end
        text plan
        text result
        int self_score "信号機3色"
        string attachment_path
    }
```

## 設計判断メモ

### マルチテナント分離
- **全テナントテーブルに `company_id` を直接持たせる**（意図的な非正規化）。JOIN経由で辿らせると Global Scope の適用漏れが越境事故に直結するため
- `company_id` は常に NOT NULL。nullable にしない
- 全モデルに Global Scope を例外なく適用する

### 運営者（和青マネジメント）
- `operator` テーブルを `user` と完全分離。認証ガードもルートも分ける
- `role`: `owner`（監査ログ閲覧可）/ `staff`（閲覧・書き込み可、監査ログ不可）
- 運営者も「1社を選んで入る」方式。セッションの `active_company_id` に対し顧客と同じ Global Scope が適用される。越境クエリは顧客企業一覧の1箇所のみ
- 画面に「運営者モード：○○社を閲覧中」を常時表示
- 運営者の書き込みは `operator_audit_log` に全件記録。ドメイン上の担当者（`owner_user_id` / `user_id`）は常に顧客企業の実在ユーザーを指す

### 年度（fiscal_year）
- 会社ごとに会計年度の開始月が異なるため独立テーブル化
- `start_month` + `closing_day` から期間を自動生成（例：4月開始・20日締め → 3/21〜4/20 が第1期間）
- 生成後の個別期間は手動編集も可能
- `status` で締め済み年度の編集を制御

### 期間の持ち方（kpi_target / kpi_record）
- `period_start` / `period_end` の**日付範囲**で保持。`year_month` のような固定単位を使わない
- 月またぎの締め日（20日締め等）、年度をまたぐ週、カスタム期間すべて表現可能
- MVPの実装は `period_type = month` のみ。日次・週次・四半期は行を足すだけで対応でき、スキーマ変更もデータ移行も不要
- 同一KPI・同一 period_type 内で期間の重複・欠落がないことをバリデーションで担保

### KPI
- `polarity`: 売上=positive（上昇が good）、コスト=negative（下降が good）。達成判定の向きを自動化
- `aggregation_type`: 累計型（売上）は sum、実測型（顧客満足度）は average / latest。年間実績の集計方法が異なるため必須
- 年間目標は月次へ均等按分するボタンを用意。月ごとの手動調整も可

### 戦略目標（strategy_goal）
- `perspective` は `financial` / `customer` / `process` / `learning` の4値に固定（DBはenumではなくstring＋アプリ側バリデーションで許容値を担保。将来の視点拡張に備えマイグレーション不要にする）
- `department_id` は nullable。部門横断（全社）の戦略目標を許容するため
- `is_adopted`（採用フラグ）：一覧画面でチェックボックスとして表示・トグルする。ブレインストーミングで出た候補のうち実際に採用するものを管理する運用を想定（GitHub Issue #5 の画面仕様に基づき追加。当初のER図には無かった項目）
- `owner_user_id` / `department_id` / `fiscal_year_id` はいずれも「同一company_idに属すること」をFormRequestで明示的にバリデーションする。`exists()`ルールはGlobal Scopeを経由しないため、`->where('company_id', ...)` を必ず付与する

### 年度（fiscal_year）
- MVP時点では年度管理画面（開始月・締め日からの期間自動生成）は未実装。年度は`company_id`+`year`の一意制約のみを持つ最小構成で先行作成し、tinker等で手動作成する運用とする
- 期間自動生成・締め済み年度の編集制御（`status`）は今後のバージョンで実装

### 認証
- Laravel標準（Breeze / Fortify）を採用。Cognitoは商用化後に載せ替えを検討
- 顧客と運営者はマルチ認証ガードで二系統に分離
- `users.email` は**テナント横断でグローバルに一意**とする（company_idごとの一意制約にはしない）。ログイン時の資格情報照合はテナント文脈が確立する前に走るため、company_idでの絞り込みができない制約に合わせた設計判断
  - この方針により、**同一人物が複数の顧客企業に所属することはできない**（1メールアドレス＝1社の1ユーザー）。兼務・出向などで複数社に関わる人物がいる場合は、現状会社ごとに別メールアドレスでアカウントを作成してもらう運用になる
  - 複数社兼務を許容する必要が出た場合は、usersとcompaniesの関係を多対多に見直すスキーマ変更が必要になる（v1.0スコープ外）

### MVP スコープ外（構造だけ用意）
- ゲーミフィケーション、戦略マップGUI、ロール制御（v1はAdmin一律）
- 日次・週次の入力UI

## 更新履歴

- 2026-07-08: 初版作成（Phase 0b）
- 2026-08-11: 商用化を見据えた全面改訂。operator / fiscal_year / strategy_goal / kpi / kpi_target / kpi_record / action_plan を追加し、7→11テーブル体制へ
- 2026-08-13: v0.3（認証）実装に伴い companies / departments / operators テーブルおよび users.company_id 等を実装。email のグローバル一意性を設計判断として追記
- 2026-08-16: v0.4（戦略目標）実装に伴い fiscal_years / strategy_goals テーブルを実装。strategy_goal に is_adopted（採用フラグ）を追加