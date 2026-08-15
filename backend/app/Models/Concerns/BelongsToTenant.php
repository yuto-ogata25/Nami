<?php

namespace App\Models\Concerns;

use App\Models\Scopes\CompanyScope;
use App\Support\TenantContext;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function ($model) {
            if (empty($model->company_id)) {
                $model->company_id = TenantContext::companyId();
            }
        });
    }
}
