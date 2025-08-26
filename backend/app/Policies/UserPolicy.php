<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'owner';
    }

    public function view(User $user, User $target): bool
    {
        if ($user->role === 'owner') {
            return $target->tenant_id === $user->tenant_id;
        }
        // qualquer usuário pode ver a si próprio
        return $user->id === $target->id;
    }

    public function create(User $user): bool
    {
        // Apenas superuser cria usuários
        return $user->role === 'superuser';
    }

    public function update(User $user, User $target): bool
    {
        // Owner edita usuários do seu tenant
        return $user->role === 'owner' && $target->tenant_id === $user->tenant_id;
    }

    public function delete(User $user, User $target): bool
    {
        // Owner pode excluir usuários do seu tenant
        return $user->role === 'owner' && $target->tenant_id === $user->tenant_id;
    }
}
