<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\StrategyGoalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id',
    'fiscal_year_id',
    'department_id',
    'perspective',
    'title',
    'definition',
    'importance',
    'owner_user_id',
    'is_adopted',
])]
class StrategyGoal extends Model
{
    /** @use HasFactory<StrategyGoalFactory> */
    use HasFactory, SoftDeletes, BelongsToTenant;

    /**
     * BSCの4視点。UIの絞り込み・バリデーションで共通利用する。
     */
    public const PERSPECTIVES = ['financial', 'customer', 'process', 'learning'];

    protected function casts(): array
    {
        return [
            'is_adopted' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
}
