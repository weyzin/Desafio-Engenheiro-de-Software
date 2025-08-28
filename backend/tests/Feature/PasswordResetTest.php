<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /** Mantém compatibilidade com seu fluxo atual (ok deixar ligado) */
    protected $seed = true;

    /**
     * Garante e retorna o usuário owner@acme.com mesmo sem seeders/factories.
     * Cria o tenant "acme" de forma compatível com id inteiro ou UUID.
     */
    private function ensureOwnerUser(): User
    {
        if ($u = User::where('email', 'owner@acme.com')->first()) {
            return $u;
        }

        $usersTable = (new User)->getTable();
        $cols = Schema::getColumnListing($usersTable);

        $data = [];
        if (in_array('name', $cols)) {
            $data['name'] = 'Owner';
        }
        $data['email'] = 'owner@acme.com';
        $data['password'] = Hash::make('secret-Temp123!');

        // Se o usuário tem tenant_id e a tabela tenants existe, usa/cria o tenant "acme"
        if (in_array('tenant_id', $cols) && Schema::hasTable('tenants')) {
            $tenantTable = 'tenants';

            $tenantId = DB::table($tenantTable)->where('slug', 'acme')->value('id');

            if (!$tenantId) {
                // payload mínimo
                $insert = ['slug' => 'acme'];
                if (Schema::hasColumn($tenantTable, 'name')) {
                    $insert['name'] = 'Acme Inc';
                }

                // Detecta tipo do id (inteiro autoincrement vs uuid/texto)
                $isIntegerId = false;
                try {
                    if (DB::getDriverName() === 'sqlite') {
                        $info = collect(DB::select("PRAGMA table_info({$tenantTable})"));
                        $idCol = $info->firstWhere('name', 'id');
                        $isIntegerId = $idCol && str_contains(strtolower($idCol->type ?? ''), 'int');
                    } else {
                        // Em outros drivers, assume UUID por segurança
                        $isIntegerId = false;
                    }
                } catch (\Throwable $e) {
                    $isIntegerId = false;
                }

                if ($isIntegerId) {
                    // id autoincrement
                    $tenantId = DB::table($tenantTable)->insertGetId($insert);
                } else {
                    // id uuid/texto obrigatório
                    $uuid = (string) Str::uuid();
                    $insert['id'] = $uuid;
                    DB::table($tenantTable)->insert($insert);
                    $tenantId = $uuid;
                }
            }

            if ($tenantId) {
                $data['tenant_id'] = $tenantId;
            }
        }

        if (in_array('role', $cols)) {
            $data['role'] = 'owner';
        }

        /** @var \App\Models\User $user */
        $user = User::query()->forceCreate($data);

        return $user->fresh();
    }

    /** /api/v1/auth/forgot deve responder 200 para e-mail existente */
    public function test_user_can_request_password_reset_link(): void
    {
        $user = $this->ensureOwnerUser();

        $res = $this->postJson('/api/v1/auth/forgot', ['email' => $user->email]);

        $res->assertStatus(200)->assertJsonStructure(['message']);
    }

    /** Fluxo feliz: token válido reseta a senha */
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

    /** Token inválido deve falhar com 400 */
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
