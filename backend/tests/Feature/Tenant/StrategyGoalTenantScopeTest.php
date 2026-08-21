<?php

namespace Tests\Feature\Tenant;

use App\Models\Company;
use App\Models\Department;
use App\Models\FiscalYear;
use App\Models\StrategyGoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * strategy_goals のテナント分離を検証する。
 * 他社IDを指定した参照・更新・削除・関連IDの割り当てがすべて拒否されることを確認する。
 */
class StrategyGoalTenantScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_strategy_goal_list_only_returns_own_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->for($companyA)->create();
        $fiscalYearA = FiscalYear::factory()->for($companyA)->create();
        $fiscalYearB = FiscalYear::factory()->for($companyB)->create();
        $ownerA = User::factory()->for($companyA)->create();
        $ownerB = User::factory()->for($companyB)->create();

        StrategyGoal::factory()->for($companyA)->for($fiscalYearA)->for($ownerA, 'owner')->count(2)->create();
        StrategyGoal::factory()->for($companyB)->for($fiscalYearB)->for($ownerB, 'owner')->count(3)->create();

        $response = $this->actingAs($userA, 'web')->getJson('/api/strategy-goals');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }

    public function test_cannot_view_other_companys_strategy_goal(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->for($companyA)->create();
        $fiscalYearB = FiscalYear::factory()->for($companyB)->create();
        $ownerB = User::factory()->for($companyB)->create();
        $goalB = StrategyGoal::factory()->for($companyB)->for($fiscalYearB)->for($ownerB, 'owner')->create();

        $response = $this->actingAs($userA, 'web')->getJson("/api/strategy-goals/{$goalB->id}");

        $response->assertStatus(404);
    }

    public function test_cannot_update_other_companys_strategy_goal(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->for($companyA)->create();
        $fiscalYearB = FiscalYear::factory()->for($companyB)->create();
        $ownerB = User::factory()->for($companyB)->create();
        $goalB = StrategyGoal::factory()->for($companyB)->for($fiscalYearB)->for($ownerB, 'owner')->create();

        $response = $this->actingAs($userA, 'web')->putJson("/api/strategy-goals/{$goalB->id}", [
            'fiscal_year_id' => $fiscalYearB->id,
            'perspective' => 'financial',
            'title' => '乗っ取り試行',
            'importance' => 1,
            'owner_user_id' => $ownerB->id,
        ]);

        $response->assertStatus(404);
    }

    public function test_cannot_delete_other_companys_strategy_goal(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->for($companyA)->create();
        $fiscalYearB = FiscalYear::factory()->for($companyB)->create();
        $ownerB = User::factory()->for($companyB)->create();
        $goalB = StrategyGoal::factory()->for($companyB)->for($fiscalYearB)->for($ownerB, 'owner')->create();

        $response = $this->actingAs($userA, 'web')->deleteJson("/api/strategy-goals/{$goalB->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('strategy_goals', ['id' => $goalB->id, 'deleted_at' => null]);
    }

    public function test_cannot_assign_fiscal_year_from_other_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->for($companyA)->create();
        $fiscalYearB = FiscalYear::factory()->for($companyB)->create();
        $ownerA = User::factory()->for($companyA)->create();

        $response = $this->actingAs($userA, 'web')->postJson('/api/strategy-goals', [
            'fiscal_year_id' => $fiscalYearB->id,
            'perspective' => 'financial',
            'title' => '越境した年度指定',
            'importance' => 3,
            'owner_user_id' => $ownerA->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('fiscal_year_id');
    }

    public function test_cannot_assign_owner_from_other_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->for($companyA)->create();
        $fiscalYearA = FiscalYear::factory()->for($companyA)->create();
        $ownerB = User::factory()->for($companyB)->create();

        $response = $this->actingAs($userA, 'web')->postJson('/api/strategy-goals', [
            'fiscal_year_id' => $fiscalYearA->id,
            'perspective' => 'financial',
            'title' => '越境した責任者指定',
            'importance' => 3,
            'owner_user_id' => $ownerB->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('owner_user_id');
    }

    public function test_cannot_assign_department_from_other_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->for($companyA)->create();
        $fiscalYearA = FiscalYear::factory()->for($companyA)->create();
        $ownerA = User::factory()->for($companyA)->create();
        $departmentB = Department::factory()->for($companyB)->create();

        $response = $this->actingAs($userA, 'web')->postJson('/api/strategy-goals', [
            'fiscal_year_id' => $fiscalYearA->id,
            'department_id' => $departmentB->id,
            'perspective' => 'financial',
            'title' => '越境した部門指定',
            'importance' => 3,
            'owner_user_id' => $ownerA->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('department_id');
    }
}
