<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    public function viewAny(User $user): bool
    {
        // Owner/Agent (e Superuser, se logado num tenant) podem listar
        return in_array($user->role, ['owner','agent','superuser'], true);
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        // NUNCA permitir cross-tenant pela API pública
        return $vehicle->tenant_id === $user->tenant_id;
    }

    public function create(User $user): bool
    {
        // Agentes podem criar veículos 
        return in_array($user->role, ['owner','agent'], true);
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        // Somente owner/agent do MESMO tenant
        return $vehicle->tenant_id === $user->tenant_id
            && in_array($user->role, ['owner','agent'], true);
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        // Apenas OWNER pode excluir veículos (mesmo tenant)
        return $vehicle->tenant_id === $user->tenant_id
            && $user->role === 'owner';
    }
}
