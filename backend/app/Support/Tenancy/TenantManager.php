<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;
use App\Models\TenantDomain;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class TenantManager
{
    protected ?Tenant $current = null;

    public function current(): ?Tenant
    {
        return $this->current;
    }

    public function set(Tenant $tenant): void
    {
        $this->current = $tenant;
        Log::withContext(['tenant_id' => $tenant->id]);
        App::instance(self::class, $this); // garante que o container conhece o estado atual
    }

    public function clear(): void
    {
        $this->current = null;
    }

    public function byDomain(string $host): ?Tenant
    {
        $domain = mb_strtolower($host);
        $td = TenantDomain::query()->whereRaw('LOWER(domain) = ?', [$domain])->first();
        return $td?->tenant ?? null;
    }

    public function bySubdomain(string $host): ?Tenant
    {
        // Ex.: acme.app.com → acme
        $parts = explode('.', $host);
        if (count($parts) < 3) return null;
        $slug = mb_strtolower($parts[0] ?? '');
        if (!$slug) return null;
        return Tenant::query()->whereRaw('LOWER(slug) = ?', [$slug])->first();
    }

    public function bySlug(string $slug): ?Tenant
    {
        return Tenant::query()->whereRaw('LOWER(slug) = ?', [mb_strtolower($slug)])->first();
    }

    public function allowsHeaderInThisEnv(): bool
    {
        // Header X-Tenant permitido apenas em local/testing/admin
        return app()->environment(['local', 'testing']) || config('app.allow_tenant_header', false);
    }
}
