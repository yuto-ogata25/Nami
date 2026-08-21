<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Kpi */
class KpiResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'strategy_goal_id' => $this->strategy_goal_id,
            'strategy_goal_title' => $this->whenLoaded('strategyGoal', fn () => $this->strategyGoal?->title),
            'perspective' => $this->whenLoaded('strategyGoal', fn () => $this->strategyGoal?->perspective),
            'department_name' => $this->whenLoaded(
                'strategyGoal',
                fn () => $this->strategyGoal?->department?->name
            ),
            'name' => $this->name,
            'definition' => $this->definition,
            'owner_user_id' => $this->owner_user_id,
            'owner_name' => $this->whenLoaded('owner', fn () => $this->owner?->name),
            'importance' => $this->importance,
            'unit' => $this->unit,
            'polarity' => $this->polarity,
            'aggregation_type' => $this->aggregation_type,
            'note' => $this->note,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
