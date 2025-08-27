<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Tenant;

class TenantPolicy
{
    public function before(User $user): ?bool
    {
        return $user->role === 'superuser' ? true : null;
    }

    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Tenant $t): bool { return true; }
    public function create(User $user): bool { return true; }
    public function update(User $user, Tenant $t): bool { return true; }
    public function delete(User $user, Tenant $t): bool { return true; }
}
