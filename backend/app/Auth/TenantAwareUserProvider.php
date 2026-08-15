<?php

namespace App\Auth;

use App\Models\Scopes\CompanyScope;
use Illuminate\Auth\EloquentUserProvider;

/**
 * users ガード専用の認証プロバイダ。
 *
 * なぜ withoutGlobalScope が必要か：
 * ログイン時の資格情報照合（email/passwordの突合）や remember token によるユーザー再取得は、
 * 「誰としてログインしているか」というテナント文脈が確立する“前”に実行される。
 * そのため User モデルの CompanyScope（company_id での絞り込み）をそのまま適用すると
 * 常に 0 件になり、どのユーザーもログインできなくなってしまう。
 * email はテナント横断でグローバルに一意な値として運用する方針（設計判断として
 * docs/database/er-diagram.md に記載）のため、このプロバイダ内の資格情報照合クエリに限り
 * CompanyScope を外す。ここ以外（コントローラ等の通常クエリ）でこのパターンを流用しないこと。
 */
class TenantAwareUserProvider extends EloquentUserProvider
{
    public function newModelQuery($model = null)
    {
        return parent::newModelQuery($model)->withoutGlobalScope(CompanyScope::class);
    }
}
