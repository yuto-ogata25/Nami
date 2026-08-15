<?php

namespace App\Models\Scopes;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $companyId = TenantContext::companyId();

        // テナント文脈を解決できない場合に全件を返してしまうと越境事故に直結するため、
        // fail-closed（0件）にする。全社横断が必要なクエリは「顧客企業一覧」の1箇所のみで許可する。
        $builder->where($model->qualifyColumn('company_id'), $companyId ?? 0);
    }
}
