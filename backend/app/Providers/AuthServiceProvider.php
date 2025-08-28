<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Vehicle;
use App\Models\User;
use App\Policies\VehiclePolicy;
use App\Policies\UserPolicy;
use App\Policies\TenantPolicy;
use App\Models\Tenant;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Vehicle::class => VehiclePolicy::class,
        User::class    => UserPolicy::class,
        Tenant::class    => TenantPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
