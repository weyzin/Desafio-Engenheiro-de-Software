<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Support\Tenancy\TenantManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TenantResolver
{
    public function __construct(private TenantManager $tm) {}

    public function handle(Request $request, Closure $next)
    {
        $host   = strtolower($request->getHost());
        $rid    = $request->attributes->get('request_id') ?? $request->headers->get('X-Request-Id');
        $allows = $this->tm->allowsHeaderInThisEnv();
        $env    = app()->environment();

        // zera sempre: evita herdar tenant entre requests
        $this->tm->clear();

        Log::info('tenancy.resolve.start', [
            'request_id' => $rid,
            'rid'        => $rid,
            'path'       => ltrim($request->path(), '/'),
            'host'       => $host,
            'x_tenant'   => $request->headers->get('X-Tenant'),
            'allowsHdr'  => $allows,
            'env'        => $env,
        ]);

        // ⚠️ Libera rotas de auth *e* /me do requisito de tenant.
        // O /me precisa funcionar para superuser sem X-Tenant.
        if ($request->is('api/v1/auth/*') || $request->is('api/v1/me')) {
            if ($allows && $request->hasHeader('X-Tenant')) {
                $slug = strtolower($request->header('X-Tenant'));
                if ($t = $this->tm->bySlug($slug)) {
                    $this->tm->set($t);
                    $request->attributes->set('tenant_id',   $t->id);
                    $request->attributes->set('tenant_slug', $t->slug ?? null);
                    Log::info('tenancy.resolve.header.ok.auth_or_me', ['rid' => $rid, 'tenant' => $t->slug]);
                } else {
                    Log::notice('tenancy.resolve.header.unknown.auth_or_me', ['rid' => $rid, 'slug' => $slug]);
                    // não derruba: /me e /auth podem operar sem tenant
                }
            }
            return $next($request);
        }

        // --- fluxo normal (demais rotas precisam de tenant) ---
        $resolved = null;

        if ($allows && $request->hasHeader('X-Tenant')) {
            $slug = strtolower($request->header('X-Tenant'));
            if ($t = $this->tm->bySlug($slug)) {
                $resolved = $t;
                Log::info('tenancy.resolve.header.ok', ['rid' => $rid, 'tenant' => $t->slug]);
            } else {
                Log::notice('tenancy.resolve.header.unknown', ['rid' => $rid, 'slug' => $slug]);
                throw new NotFoundHttpException('NOT_FOUND');
            }
        }

        if (!$resolved && ($t = $this->tm->byDomain($host))) {
            $resolved = $t;
            Log::info('tenancy.resolve.domain.ok', ['rid' => $rid, 'tenant' => $t->slug]);
        }

        if (!$resolved && ($t = $this->tm->bySubdomain($host))) {
            $resolved = $t;
            Log::info('tenancy.resolve.subdomain.ok', ['rid' => $rid, 'tenant' => $t->slug]);
        }

        if (!$resolved) {
            if ($allows) {
                Log::notice('tenancy.resolve.header.required', ['rid' => $rid]);
                throw new BadRequestHttpException('TENANT_HEADER_REQUIRED');
            }
            Log::warning('tenancy.resolve.missing', ['rid' => $rid, 'host' => $host]);
            throw new NotFoundHttpException('NOT_FOUND');
        }

        $this->tm->set($resolved);
        $request->attributes->set('tenant_id',   $resolved->id);
        $request->attributes->set('tenant_slug', $resolved->slug ?? null);

        return $next($request);
    }
}
