<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /** Log padronizado com request id, rota e tenant */
    private function flog(string $event, array $ctx = [], string $level = 'info'): void
    {
        try {
            $rid = request()->attributes->get('rid') ?? request()->header('X-Request-Id');
        } catch (\Throwable) {
            $rid = null;
        }

        $base = [
            'request_id' => $rid,
            'rid'        => $rid,
            'path'       => request()->path(),
        ];

        // anexa tenant atual se disponível
        try {
            $tm = app(TenantManager::class);
            $base['tenant'] = $tm?->id();
        } catch (\Throwable) {
            // ignore
        }

        Log::log($level, $event, array_merge($base, $ctx));
    }

    /** POST /api/v1/auth/login */
    public function login(Request $request, TenantManager $tm)
    {
        $data = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);

        $tenantId = $tm->id();
        $this->flog('auth.login.start', ['email' => $data['email'], 'tenant_id' => $tenantId]);

        // Busca usuário escopado ao tenant atual (TenantScope cuida do where tenant_id)
        $user = User::where('email', $data['email'])->first(['id','email','password','role','tenant_id']);

        if (! $user) {
            $this->flog('auth.login.user_not_found', ['email' => $data['email']]);
            return response()->json(['code' => 'INVALID_CREDENTIALS'], 401);
        }

        // Segurança extra: bater o tenant explicitamente
        if ($user->tenant_id !== $tenantId) {
            $this->flog('auth.login.tenant_mismatch', [
                'email' => $data['email'],
                'user_tid' => $user->tenant_id,
                'current_tid' => $tenantId,
            ], 'warning');

            // Para não vazar informação, trate como credencial inválida
            return response()->json(['code' => 'INVALID_CREDENTIALS'], 401);
        }

        $hashOk = Hash::check($data['password'], $user->password);
        $this->flog('auth.login.hash_check', ['ok' => $hashOk, 'uid' => $user->id]);

        if (! $hashOk) {
            return response()->json(['code' => 'INVALID_CREDENTIALS'], 401);
        }

        // Autentica via sessão (middleware de sessão já está no pipeline)
        Auth::login($user, false);
        $request->session()->regenerate();

        $this->flog('auth.login.ok', ['uid' => $user->id]);

        return response()->json([
            'data' => [
                'id'        => $user->id,
                'email'     => $user->email,
                'role'      => $user->role,
                'tenant_id' => $user->tenant_id,
            ],
        ], 200);
    }

    /** GET /api/v1/me (autenticado) */
    public function me(Request $request)
    {
        $u = Auth::user();
        if (! $u) {
            return response()->json(['code' => 'UNAUTHENTICATED'], 401);
        }

        return response()->json([
            'data' => [
                'id'        => $u->id,
                'email'     => $u->email,
                'role'      => $u->role,
                'tenant_id' => $u->tenant_id,
            ],
        ], 200);
    }

    /** POST /api/v1/auth/logout (autenticado) */
    public function logout(Request $request)
    {
        $u = Auth::user();
        $this->flog('auth.logout.start', ['uid' => $u?->id]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $this->flog('auth.logout.ok');

        return response()->noContent();
    }

    /** POST /api/v1/auth/forgot — simples/idempotente */
    public function forgot(Request $request)
    {
        $email = (string) $request->input('email');
        $this->flog('auth.forgot', ['email' => $email]);

        return response()->json([
            'message' => 'If the email exists, we will send reset instructions.',
        ], 200);
    }
}
