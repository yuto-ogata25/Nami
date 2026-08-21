<?php

namespace App\Http\Requests;

use App\Models\StrategyGoal;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StrategyGoalRequest extends FormRequest
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
            'fiscal_year_id' => [
                'required',
                Rule::exists('fiscal_years', 'id')->where('company_id', $companyId),
            ],
            'department_id' => [
                'nullable',
                Rule::exists('departments', 'id')->where('company_id', $companyId),
            ],
            'perspective' => ['required', Rule::in(StrategyGoal::PERSPECTIVES)],
            'title' => ['required', 'string', 'max:255'],
            'definition' => ['nullable', 'string'],
            'importance' => ['required', 'integer', 'min:1', 'max:5'],
            'owner_user_id' => [
                'required',
                Rule::exists('users', 'id')->where('company_id', $companyId),
            ],
            'is_adopted' => ['sometimes', 'boolean'],
        ];
    }
}
