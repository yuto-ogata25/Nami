<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Kpi;
use App\Models\StrategyGoal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kpi>
 */
class KpiFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'strategy_goal_id' => StrategyGoal::factory(),
            'name' => fake()->words(3, true),
            'definition' => fake()->sentence(),
            'owner_user_id' => User::factory(),
            'importance' => fake()->numberBetween(1, 5),
            'unit' => fake()->randomElement(['円', '%', '件', '人']),
            'polarity' => fake()->randomElement(Kpi::POLARITIES),
            'aggregation_type' => fake()->randomElement(Kpi::AGGREGATION_TYPES),
            'measurement_cycle' => 'monthly',
            'note' => null,
        ];
    }
}
