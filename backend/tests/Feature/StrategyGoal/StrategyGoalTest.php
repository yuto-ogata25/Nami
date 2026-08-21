<?php

namespace Tests\Feature\StrategyGoal;

use App\Models\Company;
use App\Models\Department;
use App\Models\FiscalYear;
use App\Models\StrategyGoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StrategyGoalTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsCustomer(): array
    {
        $company = Company::factory()->create();
        $user = User::factory()->for($company)->create();
        $this->actingAs($user, 'web');

        return [$company, $user];
    }

    public function test_customer_can_list_strategy_goals(): void
    {
        [$company] = $this->actingAsCustomer();
        $fiscalYear = FiscalYear::factory()->for($company)->create();
        $owner = User::factory()->for($company)->create();

        StrategyGoal::factory()->for($company)->for($fiscalYear)->for($owner, 'owner')->count(3)->create();

        $response = $this->getJson('/api/strategy-goals');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_customer_can_filter_strategy_goals_by_perspective(): void
    {
        [$company] = $this->actingAsCustomer();
        $fiscalYear = FiscalYear::factory()->for($company)->create();
        $owner = User::factory()->for($company)->create();

        StrategyGoal::factory()->for($company)->for($fiscalYear)->for($owner, 'owner')
            ->create(['perspective' => 'financial']);
        StrategyGoal::factory()->for($company)->for($fiscalYear)->for($owner, 'owner')
            ->create(['perspective' => 'customer']);

        $response = $this->getJson('/api/strategy-goals?perspective=financial');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.perspective', 'financial');
    }

    public function test_customer_can_create_strategy_goal(): void
    {
        [$company] = $this->actingAsCustomer();
        $fiscalYear = FiscalYear::factory()->for($company)->create();
        $department = Department::factory()->for($company)->create();
        $owner = User::factory()->for($company)->create();

        $response = $this->postJson('/api/strategy-goals', [
            'fiscal_year_id' => $fiscalYear->id,
            'department_id' => $department->id,
            'perspective' => 'financial',
            'title' => '売上を前年比110%にする',
            'definition' => '既存顧客の単価向上と新規開拓の両輪で伸ばす',
            'importance' => 5,
            'owner_user_id' => $owner->id,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.title', '売上を前年比110%にする');
        $this->assertDatabaseHas('strategy_goals', [
            'company_id' => $company->id,
            'title' => '売上を前年比110%にする',
        ]);
    }

    public function test_customer_can_view_strategy_goal(): void
    {
        [$company] = $this->actingAsCustomer();
        $fiscalYear = FiscalYear::factory()->for($company)->create();
        $owner = User::factory()->for($company)->create();
        $goal = StrategyGoal::factory()->for($company)->for($fiscalYear)->for($owner, 'owner')->create();

        $response = $this->getJson("/api/strategy-goals/{$goal->id}");

        $response->assertOk();
        $response->assertJsonPath('data.id', $goal->id);
    }

    public function test_customer_can_update_strategy_goal(): void
    {
        [$company] = $this->actingAsCustomer();
        $fiscalYear = FiscalYear::factory()->for($company)->create();
        $owner = User::factory()->for($company)->create();
        $goal = StrategyGoal::factory()->for($company)->for($fiscalYear)->for($owner, 'owner')->create();

        $response = $this->putJson("/api/strategy-goals/{$goal->id}", [
            'fiscal_year_id' => $fiscalYear->id,
            'department_id' => null,
            'perspective' => 'customer',
            'title' => '更新後のタイトル',
            'definition' => null,
            'importance' => 3,
            'owner_user_id' => $owner->id,
            'is_adopted' => false,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.title', '更新後のタイトル');
        $response->assertJsonPath('data.is_adopted', false);
    }

    public function test_customer_can_delete_strategy_goal(): void
    {
        [$company] = $this->actingAsCustomer();
        $fiscalYear = FiscalYear::factory()->for($company)->create();
        $owner = User::factory()->for($company)->create();
        $goal = StrategyGoal::factory()->for($company)->for($fiscalYear)->for($owner, 'owner')->create();

        $response = $this->deleteJson("/api/strategy-goals/{$goal->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('strategy_goals', ['id' => $goal->id]);
    }

    public function test_creating_strategy_goal_requires_valid_perspective(): void
    {
        [$company] = $this->actingAsCustomer();
        $fiscalYear = FiscalYear::factory()->for($company)->create();
        $owner = User::factory()->for($company)->create();

        $response = $this->postJson('/api/strategy-goals', [
            'fiscal_year_id' => $fiscalYear->id,
            'perspective' => 'invalid-perspective',
            'title' => 'test',
            'importance' => 3,
            'owner_user_id' => $owner->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('perspective');
    }

    public function test_creating_strategy_goal_requires_title(): void
    {
        [$company] = $this->actingAsCustomer();
        $fiscalYear = FiscalYear::factory()->for($company)->create();
        $owner = User::factory()->for($company)->create();

        $response = $this->postJson('/api/strategy-goals', [
            'fiscal_year_id' => $fiscalYear->id,
            'perspective' => 'financial',
            'importance' => 3,
            'owner_user_id' => $owner->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    public function test_unauthenticated_user_cannot_access_strategy_goals(): void
    {
        $response = $this->getJson('/api/strategy-goals');

        $response->assertStatus(401);
    }
}
