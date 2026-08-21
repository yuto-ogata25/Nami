<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\FiscalYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FiscalYear>
 */
class FiscalYearFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'year' => now()->year,
            'start_month' => 4,
            'closing_day' => 31,
            'status' => 'active',
        ];
    }
}
