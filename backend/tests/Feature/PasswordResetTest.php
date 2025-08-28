<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /** habilita o DatabaseSeeder em cada teste */
    protected $seed = true;

    /** /\api\/v1\/auth\/forgot deve responder 200 para e-mail existente */
    public function test_user_can_request_password_reset_link(): void
    {
        $user = User::where('email', 'owner@acme.com')->firstOrFail();

        $res = $this->postJson('/api/v1/auth/forgot', ['email' => $user->email]);

        $res->assertStatus(200)->assertJsonStructure(['message']);
    }

    /** fluxo feliz: token válido reseta a senha */
    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::where('email', 'owner@acme.com')->firstOrFail();

        $token = Password::createToken($user);
        $new  = 'NewP@ssw0rd!';

        $res = $this->postJson('/api/v1/auth/reset-password', [
            'email'                 => $user->email,
            'token'                 => $token,
            'password'              => $new,
            'password_confirmation' => $new,
        ]);

        $res->assertStatus(200)->assertJsonStructure(['message']);

        $user->refresh();
        $this->assertTrue(Hash::check($new, $user->password));
    }

    /** token inválido deve falhar com 400 */
    public function test_reset_password_fails_with_invalid_token(): void
    {
        $user = User::where('email', 'owner@acme.com')->firstOrFail();

        $res = $this->postJson('/api/v1/auth/reset-password', [
            'email'                 => $user->email,
            'token'                 => 'invalid-token',
            'password'              => 'Teste123!',
            'password_confirmation' => 'Teste123!',
        ]);

        $res->assertStatus(400)->assertJson(['message' => 'Invalid or expired token.']);
    }
}
