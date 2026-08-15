<?php

namespace Tests\Feature\Tenant;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * CompanyScope（Global Scope）による User モデルのテナント分離を検証する。
 */
class UserTenantScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_query_only_returns_own_company_users(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $usersA = User::factory()->for($companyA)->count(2)->create();
        User::factory()->for($companyB)->count(3)->create();

        $this->actingAs($usersA->first(), 'web');

        $this->assertSame(2, User::count());
        $this->assertTrue(
            User::all()->pluck('company_id')->every(fn ($id) => $id === $companyA->id)
        );
    }

    public function test_user_query_without_tenant_context_returns_no_rows(): void
    {
        $company = Company::factory()->create();
        User::factory()->for($company)->count(3)->create();

        // 未認証（テナント文脈が解決できない）状態。fail-closedであることを保証する。
        $this->assertSame(0, User::count());
    }
}
