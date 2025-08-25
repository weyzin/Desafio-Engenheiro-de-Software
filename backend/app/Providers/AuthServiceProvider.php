<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Vehicle;
use App\Models\User;
use App\Policies\VehiclePolicy;
use App\Policies\UserPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Vehicle::class => VehiclePolicy::class,
        User::class    => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
