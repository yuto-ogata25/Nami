<?php

namespace App\Http\Requests;

use App\Models\Kpi;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KpiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // exists() は素のDBクエリでGlobal Scopeを経由しないため、
        // company_id を明示的に絞り込んで越境参照を防ぐ。
        $companyId = $this->user('web')->company_id;

        return [
            'strategy_goal_id' => [
                'required',
                Rule::exists('strategy_goals', 'id')->where('company_id', $companyId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'definition' => ['nullable', 'string'],
            'owner_user_id' => [
                'required',
                Rule::exists('users', 'id')->where('company_id', $companyId),
            ],
            'importance' => ['required', 'integer', 'min:1', 'max:5'],
            'unit' => ['required', 'string', 'max:50'],
            'polarity' => ['required', Rule::in(Kpi::POLARITIES)],
            'aggregation_type' => ['required', Rule::in(Kpi::AGGREGATION_TYPES)],
            'note' => ['nullable', 'string'],
        ];
    }
}
