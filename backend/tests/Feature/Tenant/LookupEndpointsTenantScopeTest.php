<?php

namespace Tests\Feature\Tenant;

use App\Models\Company;
use App\Models\Department;
use App\Models\FiscalYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 戦略目標フォームのプルダウン用に追加した一覧系エンドポイント（users/departments/fiscal-years）が
 * 他社のデータを一切含まないことを検証する。
 */
class LookupEndpointsTenantScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_index_only_returns_own_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->for($companyA)->create();
        User::factory()->for($companyB)->count(2)->create();

        $response = $this->actingAs($userA, 'web')->getJson('/api/users');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_departments_index_only_returns_own_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->for($companyA)->create();
        Department::factory()->for($companyA)->create();
        Department::factory()->for($companyB)->count(2)->create();

        $response = $this->actingAs($userA, 'web')->getJson('/api/departments');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_fiscal_years_index_only_returns_own_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->for($companyA)->create();
        FiscalYear::factory()->for($companyA)->create();
        FiscalYear::factory()->for($companyB)->count(2)
            ->sequence(fn ($sequence) => ['year' => now()->year - $sequence->index])
            ->create();

        $response = $this->actingAs($userA, 'web')->getJson('/api/fiscal-years');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }
}
