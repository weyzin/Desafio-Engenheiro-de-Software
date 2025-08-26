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
        try {
            $rid = request()->attributes->get('rid') ?? request()->header('X-Request-Id');
        } catch (\Throwable) {
            $rid = null;
        }

        $base = ['request_id' => $rid, 'rid' => $rid, 'path' => request()->path()];
        try {
            $tm = app(TenantManager::class);
            $base['tenant'] = $tm?->id();
        } catch (\Throwable) { /* ignore */ }

        Log::log($level, $event, array_merge($base, $ctx));
    }

    /** POST /api/v1/auth/login — stateless (Sanctum token) */
    public function login(Request $request, TenantManager $tm)
    {
        $data = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);

        $tenantId = $tm->id(); // pode ser null se frontend não mandou X-Tenant
        $this->flog('auth.login.start', ['email' => $data['email'], 'tenant_id' => $tenantId]);

        $user = User::where('email', $data['email'])
            ->first(['id','email','password','role','tenant_id','name']);

        if (! $user) {
            $this->flog('auth.login.user_not_found', ['email' => $data['email']]);
            return response()->json(['code' => 'INVALID_CREDENTIALS'], 401);
        }

        // 🔒 Tenant match (apenas se header presente)
        // Se o frontend NÃO enviar X-Tenant, não bloqueamos o login aqui.
        if ($tenantId && $user->tenant_id !== $tenantId) {
            $this->flog('auth.login.tenant_mismatch', [
                'email'       => $data['email'],
                'user_tid'    => $user->tenant_id,
                'current_tid' => $tenantId,
            ], 'warning');
            return response()->json(['code' => 'INVALID_CREDENTIALS'], 401);
        }

        if (! Hash::check($data['password'], $user->password)) {
            $this->flog('auth.login.bad_password', ['uid' => $user->id], 'warning');
            return response()->json(['code' => 'INVALID_CREDENTIALS'], 401);
        }

        $abilities = match ($user->role) {
            'superuser' => ['*'],
            'owner'     => ['vehicles:delete','users:read'],
            'agent'     => ['users:read'],
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
                    'tenant_id' => $user->tenant_id,
                ],
            ],
        ], 200);
    }

    public function me(Request $request)
    {
        $u = $request->user();
        if (! $u) {
            return response()->json(['code' => 'UNAUTHENTICATED'], 401);
        }

        return response()->json([
            'data' => [
                'id'        => $u->id,
                'email'     => $u->email,
                'name'      => $u->name,
                'role'      => $u->role,
                'tenant_id' => $u->tenant_id,
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

        return response()->json([
            'message' => 'If the email exists, we will send reset instructions.',
        ], 200);
    }
}
