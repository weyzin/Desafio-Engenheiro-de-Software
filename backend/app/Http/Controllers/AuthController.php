<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    private function flog(string $event, array $ctx = [], string $level = 'info'): void
    {
        try { $rid = request()->attributes->get('rid') ?? request()->header('X-Request-Id'); }
        catch (\Throwable) { $rid = null; }

        $base = ['request_id' => $rid, 'rid' => $rid, 'path' => request()->path()];
        try { $tm = app(TenantManager::class); $base['tenant'] = $tm?->id(); } catch (\Throwable) {}
        Log::log($level, $event, array_merge($base, $ctx));
    }

    public function login(Request $request, TenantManager $tm)
    {
    $data = $request->validate([
        'email'    => ['required','email'],
        'password' => ['required','string'],
    ]);

    // Resolve tenant A PARTIR DO HEADER (sem depender do middleware)
    $headerSlug = strtolower((string) $request->header('X-Tenant', ''));
    $headerTenant = $headerSlug ? $tm->bySlug($headerSlug) : null;
    $tenantId = $headerTenant?->id; // pode ser null

    $this->flog('auth.login.start', ['email' => $data['email'], 'tenant_id' => $tenantId, 'slug' => $headerSlug]);

    $user = User::where('email', $data['email'])
        ->first(['id','email','password','role','tenant_id','name']);

    if (!$user) {
        $this->flog('auth.login.user_not_found', ['email' => $data['email']]);
        return response()->json(['code' => 'INVALID_CREDENTIALS'], 401);
    }

    if (!Hash::check($data['password'], $user->password)) {
        $this->flog('auth.login.bad_password', ['uid' => $user->id], 'warning');
        return response()->json(['code' => 'INVALID_CREDENTIALS'], 401);
    }

    // Regras:
    // - SUPERUSER: só permite login se NÃO houver X-Tenant
    // - OWNER/AGENT: exigimos tenant header e ele deve bater
    if ($user->role === 'superuser') {
        if ($tenantId) {
            $this->flog('auth.login.super_with_tenant', ['uid' => $user->id, 'tenant_header' => $tenantId], 'warning');
            return response()->json(['code' => 'SUPERUSER_TENANT_NOT_ALLOWED', 'message' => 'Superuser deve logar sem tenant.'], 400);
        }
        // zera qualquer contexto de tenant (só por garantia)
        $tm->clear();
    } else {
        if (!$tenantId || $user->tenant_id !== $tenantId) {
            $this->flog('auth.login.tenant_mismatch', [
                'uid' => $user->id, 'user_tid' => $user->tenant_id, 'header_tid' => $tenantId
            ], 'warning');
            return response()->json(['code' => 'INVALID_CREDENTIALS'], 401);
        }
    }

    $abilities = match ($user->role) {
        'superuser' => ['*'],
        'owner'     => ['vehicles:read','vehicles:write','vehicles:delete','users:read','users:write'],
        'agent'     => ['vehicles:read','vehicles:write','users:read'],
        default     => []
    };

    $tokenName = sprintf('api-%s-%s', $user->tenant_id ?? 'none', now()->toIso8601String());
    $token = $user->createToken($tokenName, $abilities)->plainTextToken;

    $this->flog('auth.login.ok', ['uid' => $user->id]);

    return response()->json([
        'data' => [
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => [
                'id'        => $user->id,
                'email'     => $user->email,
                'name'      => $user->name,
                'role'      => $user->role,
                'tenant_id' => $user->tenant_id, // null p/ superuser
            ],
        ],
    ], 200);
    }


    public function me(Request $request)
    {
        $u = $request->user();
        if (!$u) return response()->json(['code' => 'UNAUTHENTICATED'], 401);

        $activeTenant = null;
        try { $tm = app(TenantManager::class); $activeTenant = $tm?->id(); } catch (\Throwable) {}

        return response()->json([
            'data' => [
                'id'            => $u->id,
                'email'         => $u->email,
                'name'          => $u->name,
                'role'          => $u->role,
                'tenant_id'     => $u->tenant_id,
                'active_tenant' => $activeTenant,
            ],
        ], 200);
    }

    public function logout(Request $request)
    {
        if ($request->user() && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }
        return response()->noContent();
    }

    public function forgot(Request $request)
    {
        $email = (string) $request->input('email');
        $this->flog('auth.forgot', ['email' => $email]);

        return response()->json(['message' => 'If the email exists, we will send reset instructions.'], 200);
    }
}
