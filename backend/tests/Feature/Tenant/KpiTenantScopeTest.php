<?php

namespace Tests\Feature\Tenant;

use App\Models\Company;
use App\Models\Kpi;
use App\Models\StrategyGoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * kpis のテナント分離を検証する。
 * 他社IDを指定した参照・更新・削除・関連IDの割り当てがすべて拒否されることを確認する。
 */
class KpiTenantScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_kpi_list_only_returns_own_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->for($companyA)->create();
        $goalA = StrategyGoal::factory()->for($companyA)->create();
        $goalB = StrategyGoal::factory()->for($companyB)->create();
        $ownerA = User::factory()->for($companyA)->create();
        $ownerB = User::factory()->for($companyB)->create();

        Kpi::factory()->for($companyA)->for($goalA, 'strategyGoal')->for($ownerA, 'owner')->count(2)->create();
        Kpi::factory()->for($companyB)->for($goalB, 'strategyGoal')->for($ownerB, 'owner')->count(3)->create();

        $response = $this->actingAs($userA, 'web')->getJson('/api/kpis');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }

    public function test_cannot_view_other_companys_kpi(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->for($companyA)->create();
        $goalB = StrategyGoal::factory()->for($companyB)->create();
        $ownerB = User::factory()->for($companyB)->create();
        $kpiB = Kpi::factory()->for($companyB)->for($goalB, 'strategyGoal')->for($ownerB, 'owner')->create();

        $response = $this->actingAs($userA, 'web')->getJson("/api/kpis/{$kpiB->id}");

        $response->assertStatus(404);
    }

    public function test_cannot_update_other_companys_kpi(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->for($companyA)->create();
        $goalB = StrategyGoal::factory()->for($companyB)->create();
        $ownerB = User::factory()->for($companyB)->create();
        $kpiB = Kpi::factory()->for($companyB)->for($goalB, 'strategyGoal')->for($ownerB, 'owner')->create();

        $response = $this->actingAs($userA, 'web')->putJson("/api/kpis/{$kpiB->id}", [
            'strategy_goal_id' => $goalB->id,
            'name' => '乗っ取り試行',
            'owner_user_id' => $ownerB->id,
            'importance' => 1,
            'unit' => '円',
            'polarity' => 'positive',
            'aggregation_type' => 'sum',
        ]);

        $response->assertStatus(404);
    }

    public function test_cannot_delete_other_companys_kpi(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->for($companyA)->create();
        $goalB = StrategyGoal::factory()->for($companyB)->create();
        $ownerB = User::factory()->for($companyB)->create();
        $kpiB = Kpi::factory()->for($companyB)->for($goalB, 'strategyGoal')->for($ownerB, 'owner')->create();

        $response = $this->actingAs($userA, 'web')->deleteJson("/api/kpis/{$kpiB->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('kpis', ['id' => $kpiB->id, 'deleted_at' => null]);
    }

    public function test_cannot_assign_strategy_goal_from_other_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->for($companyA)->create();
        $goalB = StrategyGoal::factory()->for($companyB)->create();
        $ownerA = User::factory()->for($companyA)->create();

        $response = $this->actingAs($userA, 'web')->postJson('/api/kpis', [
            'strategy_goal_id' => $goalB->id,
            'name' => '越境した戦略目標指定',
            'owner_user_id' => $ownerA->id,
            'importance' => 3,
            'unit' => '円',
            'polarity' => 'positive',
            'aggregation_type' => 'sum',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('strategy_goal_id');
    }

    public function test_cannot_assign_owner_from_other_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->for($companyA)->create();
        $goalA = StrategyGoal::factory()->for($companyA)->create();
        $ownerB = User::factory()->for($companyB)->create();

        $response = $this->actingAs($userA, 'web')->postJson('/api/kpis', [
            'strategy_goal_id' => $goalA->id,
            'name' => '越境した責任者指定',
            'owner_user_id' => $ownerB->id,
            'importance' => 3,
            'unit' => '円',
            'polarity' => 'positive',
            'aggregation_type' => 'sum',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('owner_user_id');
    }
}
