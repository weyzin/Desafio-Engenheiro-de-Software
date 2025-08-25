<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Support\Tenancy\TenantManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TenantResolver
{
    public function __construct(private TenantManager $tm) {}

    public function handle(Request $request, Closure $next)
    {
        $host = strtolower($request->getHost());

        // 1) Domínio customizado
        if (!$this->tm->current() && ($t = $this->tm->byDomain($host))) {
            $this->tm->set($t);
        }

        // 2) Subdomínio padrão
        if (!$this->tm->current() && ($t = $this->tm->bySubdomain($host))) {
            $this->tm->set($t);
        }

        // 3) Fallback: X-Tenant apenas em dev/admin
        if (!$this->tm->current() && $this->tm->allowsHeaderInThisEnv()) {
            if ($request->hasHeader('X-Tenant')) {
                $slug = strtolower($request->header('X-Tenant'));
                if ($t = $this->tm->bySlug($slug)) {
                    $this->tm->set($t);
                } else {
                    // Tenant inexistente → 404 para não vazar existência
                    throw new NotFoundHttpException('NOT_FOUND');
                }
            } else {
                // Em dev/admin, X-Tenant ausente pode ser 400 explícito
                throw new BadRequestHttpException('TENANT_HEADER_REQUIRED');
            }
        }

        if (!$this->tm->current()) {
            // Produção sem resolução por domínio/subdomínio → 404
            throw new NotFoundHttpException('NOT_FOUND');
        }

        return $next($request);
    }
}
