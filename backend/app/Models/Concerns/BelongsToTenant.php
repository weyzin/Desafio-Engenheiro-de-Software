<?php

namespace App\Models\Concerns;

use App\Support\Tenancy\TenantManager;
use App\Support\Tenancy\TenantScope;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        // aplica o escopo global por tenant
        static::addGlobalScope(new TenantScope);

        // ao criar, preenche tenant_id a partir do TenantManager -> id()
        static::creating(function ($model) {
            /** @var TenantManager $tm */
            $tm = app(TenantManager::class);
            $tenantId = $tm->id();
            if ($tenantId && empty($model->tenant_id)) {
                $model->tenant_id = $tenantId;
            }
        });
    }
}
