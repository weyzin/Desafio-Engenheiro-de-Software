<?php

namespace App\Models\Concerns;

use App\Support\Tenancy\TenantScope;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            $tm = app(TenantScope::tenantManager());
            if ($tm->current() && empty($model->tenant_id)) {
                $model->tenant_id = $tm->current()->id;
            }
        });
    }
}
