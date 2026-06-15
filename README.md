# Nami 🌊

## コンセプト

**「書いたのに誰にも届かない」を仕組みで解決するエンジニア専用ブログプラットフォーム。**

波のように、全エンジニアに平等に届く。境界なく、連鎖する。  

### なぜ「Nami（波）」か

```
波は誰にも平等に届く
波は境界がない
波は連鎖する → 記事が次の人へ届くコンセプトと完全一致
```

### 静かなブログ vs うるさいブログ

| 静かなブログ（現状） | うるさいブログ（目指す世界） |
|---|---|
| 誰も読まない | 必ず誰かに届く |
| 誰にも気づかれない | 強制的に気づかれる |
| いいねがつかない | リアクション必須 |
| 議論が生まれない | 議論が設計上起きる |
| モチベ自己管理 | 仕組みがモチベを作る |

---

## MVP コア機能（3つ）

```
1. 必ず誰かに届く    → 投稿したら自動で3人にプッシュ通知
2. 強制的に気づかれる → 通知は既読するまで消えない
3. 仕組みがモチベを作る → コメントしないと自分の記事も届かなくなる
```

---

## MVP 画面一覧（5画面）

| # | 画面名 | 主な機能 |
|---|---|---|
| 1 | 記事投稿画面 | Markdown記事の作成・タイプ選択 |
| 2 | 記事一覧画面 | タイプ別フィード表示 |
| 3 | 記事詳細画面 | 記事本文 + コメント欄（必須） |
| 4 | プロフィール画面 | タイプ表示・発信数・バッジ |
| 5 | 設定画面 | タイプ内/外配信の切り替え |

---

## タイプ制（職種別）

エンジニアの職種をタイプとして定義する。
優劣なし。どのタイプも社会（地球）に必要な存在。

```
インフラ / 開発（フロント・バック） / ML・AI / セキュリティ / モバイル / ...
```

- 基本はタイプ内配信
- 設定でタイプ外にも配信可能
- 複数職種 = 複合タイプ（希少・強い）
- 強さの軸 = 発信力・発信数

---

## 技術スタック

### アプリケーション

| レイヤー | 技術 | 理由 |
|---|---|---|
| フロントエンド | Next.js (App Router) | 経験あり・SSR対応 |
| バックエンド | Laravel (PHP) | 現場で使用中・設計経験を積む |
| DB | MySQL | リレーショナル設計が必要なため |
| 認証 | Amazon Cognito | AWSサービスの実運用経験を積む |
| 通知 | Amazon SNS + Web Push API | プッシュ通知の核心機能 |
| ストレージ | Amazon S3 | 画像・ファイル管理 |

### インフラ

| 項目 | 技術 | 理由 |
|---|---|---|
| コンテナ | Docker / ECS Fargate | 環境差異ゼロ・スケール対応 |
| CI/CD | GitHub Actions → ECR → ECS | 自動デプロイ・ヒューマンエラー排除 |
| IaC | Terraform | インフラのコード化・再現性 |
| 開発環境 | Docker Compose + LocalStack | コストゼロでAWS環境を再現 |
| 本番環境 | AWS（面接前のみ起動） | コスト最適化 |

### コスト設計

```
開発中    : $0（LocalStack + Docker Compose）
本番起動時 : 約$20〜30/月（ECS Fargate + RDS片系）
運用方針  : 面接・デモ時のみ本番デプロイ
```

---

## ローカル起動手順

```bash
# リポジトリクローン
git clone https://github.com/ogazon/urusai-blog.git
cd urusai-blog

# 環境変数設定
cp .env.example .env

# Docker起動（LocalStack含む）
docker compose up -d

# Laravelセットアップ
docker compose exec app composer install
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed

# フロントエンドセットアップ
cd frontend
npm install
npm run dev
```

---

## ディレクトリ構成（予定）

```
urusai-blog/
├── frontend/          # Next.js
│   ├── app/
│   └── components/
├── backend/           # Laravel
│   ├── app/
│   ├── routes/
│   └── database/
├── infra/             # Terraform
│   ├── ecs.tf
│   ├── rds.tf
│   └── cognito.tf
├── .github/
│   └── workflows/     # GitHub Actions
│       └── deploy.yml
└── docker-compose.yml
```

---

## 開発ロードマップ

```
Phase 1（MVP）
  └ Docker環境構築
  └ 認証（Cognito）
  └ 記事投稿・一覧・詳細
  └ プッシュ通知（SNS）
  └ コメント必須ロジック
  └ botアカウントでシミュレーション

Phase 2（タイプ制）
  └ タイプ設定
  └ タイプ別配信ロジック
  └ 複合タイプ対応

Phase 3（称号・モチベ設計）
  └ バッジシステム
  └ 発信力スコア
  └ プロフィール強化

Phase 4（本番デプロイ）
  └ Terraform本番環境構築
  └ CI/CDパイプライン本番接続
  └ ECS Fargate稼働確認
```

---

## このプロジェクトについて

サービス名: Nami 🌊  
作者: Gaddy（ogazon）  
Zenn: https://zenn.dev/ogazon  
目的: 日本のエンジニアアウトプット人口増加、アウトプット課題解決
ブランド: Gaddy
