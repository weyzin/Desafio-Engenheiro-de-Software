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
        $host    = strtolower($request->getHost());
        $rid     = $request->attributes->get('request_id') ?? $request->headers->get('X-Request-Id');
        $xTenant = $request->headers->get('X-Tenant');
        $allows  = $this->tm->allowsHeaderInThisEnv();
        $env     = app()->environment();
        $inCli   = app()->runningInConsole();

        Log::info('tenancy.resolve.start', [
            'request_id' => $rid,
            'rid'        => $rid,
            'path'       => ltrim($request->path(), '/'),
            'host'       => $host,
            'x_tenant'   => $xTenant,
            'allowsHdr'  => $allows,
            'env'        => $env,
            'in_console' => $inCli,
        ]);

        // Sempre propaga o tenant resolvido p/ a Request
        $setRequestTenant = function ($tenant) use ($request) {
            if ($tenant) {
                $request->attributes->set('tenant_id', $tenant->id);
                $request->attributes->set('tenant_slug', $tenant->slug ?? null);
            }
        };

        $resolved = null;

        // 1) Header tem prioridade quando permitido — e deve sobrescrever o tenant atual
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

        // 2) Domínio customizado (se nada foi resolvido pelo header)
        if (!$resolved && ($t = $this->tm->byDomain($host))) {
            $resolved = $t;
            Log::info('tenancy.resolve.domain.ok', ['rid' => $rid, 'tenant' => $t->slug]);
        }

        // 3) Subdomínio (slug.dominio.com) (se ainda não resolveu)
        if (!$resolved && ($t = $this->tm->bySubdomain($host))) {
            $resolved = $t;
            Log::info('tenancy.resolve.subdomain.ok', ['rid' => $rid, 'tenant' => $t->slug]);
        }

        // 4) Caso não resolva:
        if (!$resolved) {
            if ($allows) {
                // Em dev/testing/CLI sem domínio resolvido, exigimos o header
                Log::notice('tenancy.resolve.header.required', ['rid' => $rid]);
                throw new BadRequestHttpException('TENANT_HEADER_REQUIRED');
            }
            // Em produção, sem domínio/subdomínio resolvido → 404
            Log::warning('tenancy.resolve.missing', ['rid' => $rid, 'host' => $host]);
            throw new NotFoundHttpException('NOT_FOUND');
        }

        // Define no manager (sobrescrevendo, se necessário) e propaga na Request
        $this->tm->set($resolved);
        $setRequestTenant($resolved);

        return $next($request);
    }
}
