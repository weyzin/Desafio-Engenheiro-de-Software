<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /** Opcional: mantém compatibilidade com seu fluxo atual (não faz mal deixar) */
    protected $seed = true;

    /** Garante e retorna o usuário owner@acme.com mesmo sem seeders/factories */
    private function ensureOwnerUser(): User
    {
        if ($u = User::where('email', 'owner@acme.com')->first()) {
            return $u;
        }

        // Descobre colunas existentes para não “chutar” schema
        $usersTable = (new User)->getTable();
        $cols = Schema::getColumnListing($usersTable);

        $data = [];

        if (in_array('name', $cols)) {
            $data['name'] = 'Owner';
        }

        $data['email'] = 'owner@acme.com';
        $data['password'] = Hash::make('secret-Temp123!');

        // Se houver tenant_id, cria/usa um tenant “acme”
        if (in_array('tenant_id', $cols)) {
            // Evita dependência forte do modelo Tenant (pode não existir no CI)
            $tenantTable = 'tenants';
            if (Schema::hasTable($tenantTable)) {
                // tenta achar pelo slug; se não existir, cria o mínimo
                $tenantId = \DB::table($tenantTable)
                    ->where('slug', 'acme')
                    ->value('id');

                if (!$tenantId) {
                    $insert = ['slug' => 'acme'];
                    if (Schema::hasColumn($tenantTable, 'name')) {
                        $insert['name'] = 'Acme Inc';
                    }
                    $tenantId = \DB::table($tenantTable)->insertGetId($insert);
                }

                $data['tenant_id'] = $tenantId;
            }
        }

        if (in_array('role', $cols)) {
            $data['role'] = 'owner';
        }

        // Ignora proteção de mass assignment caso exista
        /** @var \App\Models\User $user */
        $user = User::query()->forceCreate($data);

        return $user->fresh();
    }

    /** /\api\/v1\/auth\/forgot deve responder 200 para e-mail existente */
    public function test_user_can_request_password_reset_link(): void
    {
        $user = $this->ensureOwnerUser();

        $res = $this->postJson('/api/v1/auth/forgot', ['email' => $user->email]);

        $res->assertStatus(200)->assertJsonStructure(['message']);
    }

    /** fluxo feliz: token válido reseta a senha */
    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = $this->ensureOwnerUser();

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
        $user = $this->ensureOwnerUser();

        $res = $this->postJson('/api/v1/auth/reset-password', [
            'email'                 => $user->email,
            'token'                 => 'invalid-token',
            'password'              => 'Teste123!',
            'password_confirmation' => 'Teste123!',
        ]);

        $res->assertStatus(400)->assertJson(['message' => 'Invalid or expired token.']);
    }
}
