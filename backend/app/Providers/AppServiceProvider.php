<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // garante 1 única instância do gerenciador por request/teste
        $this->app->singleton(\App\Support\Tenancy\TenantManager::class, function () {
            return new \App\Support\Tenancy\TenantManager();
        });
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
