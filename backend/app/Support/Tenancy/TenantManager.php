<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;
use Illuminate\Support\Str;

class TenantManager
{
    private const KEY_ID   = 'tenant_id';
    private const KEY_SLUG = 'tenant_slug';

    /** Usa sempre a request atual (evita problemas se o container usar singleton). */
    private function req()
    {
        return request();
    }

    public function id(): ?string
    {
        return $this->req()->attributes->get(self::KEY_ID);
    }

    public function slug(): ?string
    {
        return $this->req()->attributes->get(self::KEY_SLUG);
    }

    public function set(?Tenant $tenant): void
    {
        if ($tenant) {
            $this->req()->attributes->set(self::KEY_ID,   $tenant->id);
            $this->req()->attributes->set(self::KEY_SLUG, $tenant->slug ?? null);
        } else {
            $this->clear();
        }
    }

    public function setId(?string $tenantId, ?string $slug = null): void
    {
        if ($tenantId) {
            $this->req()->attributes->set(self::KEY_ID,   $tenantId);
            if ($slug !== null) {
                $this->req()->attributes->set(self::KEY_SLUG, $slug);
            }
        } else {
            $this->clear();
        }
    }

    public function clear(): void
    {
        $this->req()->attributes->remove(self::KEY_ID);
        $this->req()->attributes->remove(self::KEY_SLUG);
    }

    // ===== lookups =====
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
        if (!str_contains($host, '.')) return null;
        $sub = Str::before($host, '.');
        return Tenant::where('slug', $sub)->first();
    }

    public function allowsHeaderInThisEnv(): bool
    {
        if (app()->runningInConsole()) return true;
        return app()->environment(['local','testing']) || (bool) config('tenancy.allow_header', false);
    }
}
