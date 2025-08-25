<?php

namespace App\Support\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        /** @var TenantManager $tm */
        $tm = app(self::tenantManager());
        $tenant = $tm->current();

        if ($tenant) {
            $builder->where($model->getTable().'.tenant_id', $tenant->id);
        }
    }

    public static function tenantManager(): string
    {
        return TenantManager::class;
    }
}
