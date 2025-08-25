<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;
use Illuminate\Support\Str;

class TenantManager
{
    private ?Tenant $current = null;

    public function current(): ?Tenant
    {
        return $this->current;
    }

    public function set(Tenant $tenant): void
    {
        $this->current = $tenant;
    }

    public function id(): ?string
    {
        return $this->current?->id;
    }

    public function reset(): void
    {
        $this->current = null;
    }

    public function bySlug(string $slug): ?Tenant
    {
        return Tenant::where('slug', $slug)->first();
    }

    public function byDomain(string $host): ?Tenant
    {
        return Tenant::whereHas('domains', fn ($q) => $q->where('domain', $host))->first();
    }

    public function bySubdomain(string $host): ?Tenant
    {
        if (!str_contains($host, '.')) {
            return null;
        }
        $sub = Str::before($host, '.');

        return Tenant::where('slug', $sub)->first();
    }

    public function allowsHeaderInThisEnv(): bool
    {
        // Em testes, rodando via CLI, permita SEMPRE (evita 404 falso).
        if (app()->runningInConsole()) {
            return true;
        }

        // Local/testing ou flag explícita
        return app()->environment(['local', 'testing']) || (bool) config('tenancy.allow_header', false);
    }
}
