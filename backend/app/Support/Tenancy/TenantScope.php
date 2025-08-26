<?php

namespace App\Support\Tenancy;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        /** @var TenantManager $tm */
        $tm = app(TenantManager::class);
        $tenantId = $tm->id();

        // Se há tenant ativo na request, filtra por ele;
        // Se NÃO há (ex.: superuser global), não aplica filtro aqui e
        // quem chama decide o comportamento (vide VehicleController::index)
        if ($tenantId) {
            $builder->where($model->getTable().'.tenant_id', $tenantId);
        }
    }

    // Mantido para compatibilidade com código legado
    public static function tenantManager(): string
    {
        return TenantManager::class;
    }
}
