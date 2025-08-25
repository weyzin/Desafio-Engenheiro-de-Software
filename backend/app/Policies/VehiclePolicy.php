<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    public function viewAny(User $user): bool
    {
        // Owner/Agent do tenant podem listar; superuser depende de rota/admin (fora do MVP público)
        return in_array($user->role, ['owner','agent','superuser'], true);
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        if ($user->role === 'superuser') {
            // Para superuser em endpoints públicos, NÃO permitir cross-tenant por padrão
            return $vehicle->tenant_id === $user->tenant_id || is_null($user->tenant_id) === false;
        }
        return $vehicle->tenant_id === $user->tenant_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['owner','agent'], true);
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        if ($user->role === 'superuser') {
            // idem view: superuser só em área/rota administrativa (não coberta aqui)
            return false;
        }
        return $vehicle->tenant_id === $user->tenant_id && in_array($user->role, ['owner','agent'], true);
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        if ($user->role === 'superuser') {
            return false;
        }
        return $vehicle->tenant_id === $user->tenant_id && in_array($user->role, ['owner','agent'], true);
    }
}
