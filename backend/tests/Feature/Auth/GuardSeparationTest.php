<?php

namespace Tests\Feature\Auth;

use App\Models\Company;
use App\Models\Operator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 顧客ガード(web)と運営者ガード(operator)が互いに独立しており、
 * 一方のセッションでもう一方の認証済みルートに到達できないことを保証する。
 */
class GuardSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_session_cannot_access_operator_route(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->for($company)->create();

        $response = $this->actingAs($user, 'web')->getJson('/api/operator/me');

        $response->assertStatus(401);
    }

    public function test_operator_session_cannot_access_customer_route(): void
    {
        $operator = Operator::factory()->create();

        $response = $this->actingAs($operator, 'operator')->getJson('/api/user');

        $response->assertStatus(401);
    }
}
