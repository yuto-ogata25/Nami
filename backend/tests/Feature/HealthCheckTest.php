<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * /up はALBヘルスチェック、/api/health はアプリのDB疎通確認に使われる。
 * 認証必須化してしまうとECSタスクが全てunhealthy判定になりサービスが起動しなくなるため、
 * 未認証でアクセスできることを固定するテスト。
 */
class HealthCheckTest extends TestCase
{
    public function test_up_health_check_is_publicly_accessible(): void
    {
        $response = $this->get('/up');

        $response->assertStatus(200);
    }

    public function test_api_health_check_is_publicly_accessible(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'ok');
    }
}
