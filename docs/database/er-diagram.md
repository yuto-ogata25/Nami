# Nami ER図

BSC型KPI管理アプリ Nami のデータベース設計。

## 全体構造

```mermaid
erDiagram
    company ||--o{ department : ""
    department ||--o{ user : ""
    company ||--o{ strategy_goal : ""
    strategy_goal ||--o{ kpi : ""
    kpi ||--o{ monthly_record : ""
    strategy_goal ||--o{ action_plan : ""

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
    strategy_goal {
        id id
        id company_id
        id department_id
        string perspective
        string title
        string definition
        int importance
        id owner_user_id
    }
    kpi {
        id id
        id strategy_goal_id
        string name
        id owner_user_id
        string unit
        string polarity
        string note
    }
    monthly_record {
        id id
        id kpi_id
        string year_month
        decimal target_value
        decimal actual_value
    }
    action_plan {
        id id
        id strategy_goal_id
        id user_id
        string year_month
        text plan
        text result
        int self_score
        string attachment_path
    }
```

## 設計判断メモ

- **company**: 最小構成（id/name）。将来のマルチテナントSaaS化を見据えた境界線。住所・業種などは今使わないため保留（YAGNI）
- **user**: `role` / `company_id` / `department_id` を先行予約。v1はAdmin一律運用、権限制御は後続バージョンで追加
- **strategy_goal**: BSCの4視点（財務/顧客/業務プロセス/学習と成長）を `perspective` に固定値で持つ
- **kpi**: `polarity`（positive/negative）で達成判定の向きを自動化。売上=positive、コスト=negativeのように指標ごとに向きが逆
- **monthly_record**: 月次粒度で固定。週次など別粒度が必要になった場合は別テーブルとして追加する方針（既存構造を壊さない）
- **action_plan**: `self_score` は信号機3色評価。`attachment_path` は当面ローカル保存、将来的にS3へ移行想定

## 更新履歴

- 2026-07-08: 初版作成（Phase 0b）
