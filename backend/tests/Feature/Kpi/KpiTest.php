<?php

namespace Tests\Feature\Kpi;

use App\Models\Company;
use App\Models\Kpi;
use App\Models\StrategyGoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpiTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsCustomer(): array
    {
        $company = Company::factory()->create();
        $user = User::factory()->for($company)->create();
        $this->actingAs($user, 'web');

        return [$company, $user];
    }

    public function test_customer_can_list_kpis(): void
    {
        [$company] = $this->actingAsCustomer();
        $goal = StrategyGoal::factory()->for($company)->create();
        $owner = User::factory()->for($company)->create();

        Kpi::factory()->for($company)->for($goal, 'strategyGoal')->for($owner, 'owner')->count(3)->create();

        $response = $this->getJson('/api/kpis');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_kpi_list_includes_strategy_goal_perspective_and_department(): void
    {
        [$company] = $this->actingAsCustomer();
        $goal = StrategyGoal::factory()->for($company)->create(['perspective' => 'financial']);
        $owner = User::factory()->for($company)->create();
        Kpi::factory()->for($company)->for($goal, 'strategyGoal')->for($owner, 'owner')->create();

        $response = $this->getJson('/api/kpis');

        $response->assertOk();
        $response->assertJsonPath('data.0.perspective', 'financial');
        $response->assertJsonPath('data.0.strategy_goal_title', $goal->title);
    }

    public function test_customer_can_create_kpi(): void
    {
        [$company] = $this->actingAsCustomer();
        $goal = StrategyGoal::factory()->for($company)->create();
        $owner = User::factory()->for($company)->create();

        $response = $this->postJson('/api/kpis', [
            'strategy_goal_id' => $goal->id,
            'name' => '売上高',
            'definition' => '月次売上の合計',
            'owner_user_id' => $owner->id,
            'importance' => 5,
            'unit' => '円',
            'polarity' => 'positive',
            'aggregation_type' => 'sum',
            'note' => null,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.name', '売上高');
        $this->assertDatabaseHas('kpis', [
            'company_id' => $company->id,
            'name' => '売上高',
            'measurement_cycle' => 'monthly',
        ]);
    }

    public function test_measurement_cycle_is_always_forced_to_monthly(): void
    {
        [$company] = $this->actingAsCustomer();
        $goal = StrategyGoal::factory()->for($company)->create();
        $owner = User::factory()->for($company)->create();

        $response = $this->postJson('/api/kpis', [
            'strategy_goal_id' => $goal->id,
            'name' => '顧客満足度',
            'owner_user_id' => $owner->id,
            'importance' => 3,
            'unit' => '点',
            'polarity' => 'positive',
            'aggregation_type' => 'average',
            'measurement_cycle' => 'daily',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('kpis', [
            'company_id' => $company->id,
            'name' => '顧客満足度',
            'measurement_cycle' => 'monthly',
        ]);
    }

    public function test_customer_can_update_kpi(): void
    {
        [$company] = $this->actingAsCustomer();
        $goal = StrategyGoal::factory()->for($company)->create();
        $owner = User::factory()->for($company)->create();
        $kpi = Kpi::factory()->for($company)->for($goal, 'strategyGoal')->for($owner, 'owner')->create();

        $response = $this->putJson("/api/kpis/{$kpi->id}", [
            'strategy_goal_id' => $goal->id,
            'name' => '更新後の指標名',
            'owner_user_id' => $owner->id,
            'importance' => 2,
            'unit' => '件',
            'polarity' => 'negative',
            'aggregation_type' => 'latest',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.name', '更新後の指標名');
    }

    public function test_customer_can_delete_kpi(): void
    {
        [$company] = $this->actingAsCustomer();
        $goal = StrategyGoal::factory()->for($company)->create();
        $owner = User::factory()->for($company)->create();
        $kpi = Kpi::factory()->for($company)->for($goal, 'strategyGoal')->for($owner, 'owner')->create();

        $response = $this->deleteJson("/api/kpis/{$kpi->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('kpis', ['id' => $kpi->id]);
    }

    public function test_creating_kpi_requires_valid_polarity(): void
    {
        [$company] = $this->actingAsCustomer();
        $goal = StrategyGoal::factory()->for($company)->create();
        $owner = User::factory()->for($company)->create();

        $response = $this->postJson('/api/kpis', [
            'strategy_goal_id' => $goal->id,
            'name' => 'test',
            'owner_user_id' => $owner->id,
            'importance' => 3,
            'unit' => '円',
            'polarity' => 'invalid',
            'aggregation_type' => 'sum',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('polarity');
    }

    public function test_unauthenticated_user_cannot_access_kpis(): void
    {
        $response = $this->getJson('/api/kpis');

        $response->assertStatus(401);
    }
}
