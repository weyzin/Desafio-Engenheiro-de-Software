<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        // Apenas Owner lista usuários do próprio tenant
        return $user->role === 'owner';
    }

    public function view(User $user, User $target): bool
    {
        if ($user->role === 'owner') {
            return $target->tenant_id === $user->tenant_id;
        }
        return $user->id === $target->id; // cada um vê a si mesmo
    }

    public function create(User $user): bool
    {
        return $user->role === 'owner';
    }

    public function update(User $user, User $target): bool
    {
        return $user->role === 'owner' && $target->tenant_id === $user->tenant_id;
    }

    public function delete(User $user, User $target): bool
    {
        return $user->role === 'owner' && $target->tenant_id === $user->tenant_id;
    }
}
