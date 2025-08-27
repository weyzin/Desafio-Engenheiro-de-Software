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
        // Pré-flight CORS nunca deve exigir tenant
        if ($request->getMethod() === 'OPTIONS') {
            return $next($request);
        }

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

        // Rotas GLOBAIS (não exigem tenant): auth, me, users*, tenants*
        if ($request->is('api/v1/auth/*')
            || $request->is('api/v1/me')
            || $request->is('api/v1/users*')
            || $request->is('api/v1/tenants*')) {

            // Aceita X-Tenant se vier (contexto opcional p/ superuser)
            if ($allows && $request->hasHeader('X-Tenant')) {
                $slug = strtolower($request->header('X-Tenant'));
                if ($t = $this->tm->bySlug($slug)) {
                    $this->tm->set($t);
                    $request->attributes->set('tenant_id',   $t->id);
                    $request->attributes->set('tenant_slug', $t->slug ?? null);
                    Log::info('tenancy.resolve.header.ok.global', ['rid' => $rid, 'tenant' => $t->slug]);
                } else {
                    Log::notice('tenancy.resolve.header.unknown.global', ['rid' => $rid, 'slug' => $slug]);
                    // não derruba: rotas globais podem operar sem tenant
                }
            }
            return $next($request);
        }

        // --- Demais rotas: precisam de tenant resolvido ---
        $resolved = null;

        // 1) Header tem prioridade quando permitido
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

        // 2) Domínio customizado
        if (!$resolved && ($t = $this->tm->byDomain($host))) {
            $resolved = $t;
            Log::info('tenancy.resolve.domain.ok', ['rid' => $rid, 'tenant' => $t->slug]);
        }

        // 3) Subdomínio slug.dominio.com
        if (!$resolved && ($t = $this->tm->bySubdomain($host))) {
            $resolved = $t;
            Log::info('tenancy.resolve.subdomain.ok', ['rid' => $rid, 'tenant' => $t->slug]);
        }

        // 4) Falhou
        if (!$resolved) {
            if ($allows) {
                Log::notice('tenancy.resolve.header.required', ['rid' => $rid]);
                throw new BadRequestHttpException('TENANT_HEADER_REQUIRED');
            }
            Log::warning('tenancy.resolve.missing', ['rid' => $rid, 'host' => $host]);
            throw new NotFoundHttpException('NOT_FOUND');
        }

        // Seta no manager + request
        $this->tm->set($resolved);
        $request->attributes->set('tenant_id',   $resolved->id);
        $request->attributes->set('tenant_slug', $resolved->slug ?? null);

        // 🔒 Se houver usuário autenticado (via token Sanctum) e NÃO for superuser,
        // bloqueia mismatch entre tenant do usuário e o tenant resolvido.
        // (Usa o guard diretamente para independer da ordem dos middlewares.)
        $user = auth('sanctum')->user();
        if ($user && $user->role !== 'superuser' && $user->tenant_id !== $resolved->id) {
            Log::warning('tenancy.resolve.mismatch', [
                'rid'         => $rid,
                'user_id'     => $user->id,
                'user_tid'    => $user->tenant_id,
                'resolved_tid'=> $resolved->id,
            ]);

            return response()->json([
                'code'    => 'TENANT_MISMATCH',
                'message' => 'Acesso negado ao tenant informado.',
            ], 403);
        }

        return $next($request);
    }
}
