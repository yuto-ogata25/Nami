<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\FiscalYearFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['company_id', 'year', 'start_month', 'closing_day', 'status'])]
class FiscalYear extends Model
{
    /** @use HasFactory<FiscalYearFactory> */
    use HasFactory, SoftDeletes, BelongsToTenant;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function strategyGoals(): HasMany
    {
        return $this->hasMany(StrategyGoal::class);
    }
}
