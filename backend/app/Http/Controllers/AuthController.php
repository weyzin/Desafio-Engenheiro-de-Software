<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        // Sanctum SPA: cookies HttpOnly + CSRF (XSRF-TOKEN)
        $credentials = $request->only(['email','password']);

        if (!Auth::attempt($credentials, true)) {
            // 401 UNAUTHORIZED (OpenAPI usa "UNAUTHORIZED" para login inválido)
            return response()->json([
                'code'    => 'UNAUTHORIZED',
                'message' => 'Credenciais inválidas.',
            ], 401);
        }

        $user = $request->user();
        $user->forceFill(['last_login_at' => now()])->save();

        Log::info('auth.login', [
            'user_id'   => $user->id,
            'tenant_id' => $user->tenant_id,
        ]);

        // Envelope { data: User }
        return ApiResponse::item([
            'id'        => $user->id,
            'name'      => $user->name,
            'email'     => $user->email,
            'role'      => $user->role,
            'tenant_id' => $user->tenant_id,
        ], 200);
    }

    public function me(Request $request)
    {
        $u = $request->user();
        return ApiResponse::item([
            'id'        => $u->id,
            'name'      => $u->name,
            'email'     => $u->email,
            'role'      => $u->role,
            'tenant_id' => $u->tenant_id,
        ], 200)->header('Cache-Control', 'public, max-age=60');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('auth.logout', [
            'user_id'   => $request->user()?->id,
            'tenant_id' => $request->user()?->tenant_id,
        ]);

        return response()->noContent(); // 204
    }

    public function forgot(Request $request)
    {
        $request->validate(['email' => ['required','email','max:254']]);

        // Idempotente: sempre retornamos 200 com mensagem genérica
        // (Podemos disparar o broker de reset; no MVP, apenas demo)
        try {
            Password::broker()->sendResetLink(['email' => $request->input('email')]);
        } catch (\Throwable $e) {
            // Silencia para manter idempotência
        }

        return response()->json([
            'message' => 'Se este e-mail existir, a recuperação será enviada.',
        ], 200);
    }
}
