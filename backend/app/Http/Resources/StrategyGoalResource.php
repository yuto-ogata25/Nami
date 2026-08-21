<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\StrategyGoal */
class StrategyGoalResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fiscal_year_id' => $this->fiscal_year_id,
            'department_id' => $this->department_id,
            'department_name' => $this->whenLoaded('department', fn () => $this->department?->name),
            'perspective' => $this->perspective,
            'title' => $this->title,
            'definition' => $this->definition,
            'importance' => $this->importance,
            'owner_user_id' => $this->owner_user_id,
            'owner_name' => $this->whenLoaded('owner', fn () => $this->owner?->name),
            'is_adopted' => $this->is_adopted,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
