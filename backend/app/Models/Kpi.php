<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\KpiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id',
    'strategy_goal_id',
    'name',
    'definition',
    'owner_user_id',
    'importance',
    'unit',
    'polarity',
    'aggregation_type',
    'measurement_cycle',
    'note',
])]
class Kpi extends Model
{
    /** @use HasFactory<KpiFactory> */
    use HasFactory, SoftDeletes, BelongsToTenant;

    /**
     * 達成判定の向き。売上=positive（上昇が good）、コスト=negative（下降が good）。
     */
    public const POLARITIES = ['positive', 'negative'];

    /**
     * 年間実績の集計方法。累計型（売上）はsum、実測型（顧客満足度）はaverage/latest。
     */
    public const AGGREGATION_TYPES = ['sum', 'average', 'latest'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function strategyGoal(): BelongsTo
    {
        return $this->belongsTo(StrategyGoal::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
}
