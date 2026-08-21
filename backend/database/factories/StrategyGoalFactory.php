<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\FiscalYear;
use App\Models\StrategyGoal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StrategyGoal>
 */
class StrategyGoalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'fiscal_year_id' => FiscalYear::factory(),
            'department_id' => null,
            'perspective' => fake()->randomElement(StrategyGoal::PERSPECTIVES),
            'title' => fake()->sentence(4),
            'definition' => fake()->paragraph(),
            'importance' => fake()->numberBetween(1, 5),
            'owner_user_id' => User::factory(),
            'is_adopted' => true,
        ];
    }
}
