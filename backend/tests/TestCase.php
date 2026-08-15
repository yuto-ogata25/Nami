<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Sanctum の EnsureFrontendRequestsAreStateful は Origin/Referer ヘッダで
        // SPAからのリクエストかどうかを判定し、該当する場合のみセッションを開始する。
        // 本番でNext.jsが実際に送るヘッダを模して、テストでも常にフロントエンドからのリクエストとして扱う。
        $this->withHeader('Origin', env('FRONTEND_URL', 'http://localhost:3000'));
    }
}
