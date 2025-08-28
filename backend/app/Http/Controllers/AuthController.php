<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\PasswordResetLink;
use App\Support\Tenancy\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

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
        $data = $request->validate([
            'email' => ['required','email'],
        ]);
        $email = strtolower($data['email']);

        // Tenta resolver TenantManager, mas sem quebrar se não existir
        $headerSlug = strtolower((string) $request->header('X-Tenant', ''));
        $tenantId = null;
        if ($headerSlug) {
            try {
                $tm = app(\App\Support\Tenancy\TenantManager::class);
                $tenantId = $tm?->bySlug($headerSlug)?->id ?? null;
            } catch (\Throwable $e) {
                // sem tenancy binding; segue sem restringir por tenant
            }
        }

        $this->flog('auth.forgot.start', ['email' => $email, 'tenant_id' => $tenantId, 'slug' => $headerSlug]);

        $userQ = User::query()->where('email', $email);
        if ($tenantId) $userQ->where('tenant_id', $tenantId);
        $user = $userQ->first();

        if ($user) {
            try {
                $token = \Illuminate\Support\Facades\Password::createToken($user);

                $base  = env('FRONTEND_PASSWORD_RESET_URL', 'http://localhost:5173/reset-password');
                $query = http_build_query(['token' => $token, 'email' => $user->email]);
                $url   = rtrim($base, '?&') . (str_contains($base, '?') ? '&' : '?') . $query;

                $user->notify(new \App\Notifications\PasswordResetLink($url));
                $this->flog('auth.forgot.sent', ['uid' => $user->id]);
            } catch (\Throwable $e) {
                $this->flog('auth.forgot.error', ['error' => $e->getMessage()], 'warning');
                // mantém resposta idempotente
            }
        } else {
            $this->flog('auth.forgot.no_user', ['email' => $email], 'info');
        }

        return response()->json(['message' => 'If the email exists, you will receive a password reset link shortly.'], 200);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email'                 => ['required','email'],
            'token'                 => ['required','string'],
            'password'              => ['required','string','min:8','confirmed'],
        ]);

        $email = strtolower($data['email']);
        $this->flog('auth.reset.start', ['email' => $email]);

        $status = Password::reset(
            [
                'email' => $email,
                'token' => $data['token'],
                'password' => $data['password'],
                'password_confirmation' => $request->input('password_confirmation'),
            ],
            function (User $user) use ($data) {
                $user->forceFill([
                    'password' => Hash::make($data['password']),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));

                // Revoga TODOS os tokens Sanctum (logout global)
                try { $user->tokens()->delete(); } catch (\Throwable) {}
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $this->flog('auth.reset.ok', ['email' => $email]);
            return response()->json(['message' => 'Password reset successfully.'], 200);
        }

        $this->flog('auth.reset.fail', ['status' => $status], 'warning');
        return response()->json(['message' => 'Invalid or expired token.'], 400);
    }
}
