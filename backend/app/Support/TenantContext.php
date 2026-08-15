<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * 「現在のテナント（company_id）は何か」を解決する唯一の場所。
 * Global Scope からはここだけを参照させ、判定ロジックが複数箇所に散らばるのを防ぐ。
 */
class TenantContext
{
    public static function companyId(): ?int
    {
        if (Auth::guard('web')->check()) {
            return Auth::guard('web')->user()->company_id;
        }

        if (Auth::guard('operator')->check()) {
            // 運営者は「1社を選んで入る」方式。選択前は null（=どのテナントデータも見えない）。
            return Session::get('active_company_id');
        }

        return null;
    }
}
