<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RBACTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
        $this->seed();
    }

    public function test_superuser_can_create_user(): void
    {
        // login como superuser SEM X-Tenant
        $login = $this->withHeaders(['Accept'=>'application/json'])
            ->postJson('/api/v1/auth/login', [
                'email' => 'admin@root.com',
                'password' => 'Password!123',
            ])->assertStatus(200)->json('data');

        $rootToken = $login['token'];

        // superuser cria usuário no tenant ACME (usa tenant_id UUID)
        $this->withHeaders([
                'Accept'        => 'application/json',
                'Authorization' => "Bearer {$rootToken}",
            ])->postJson('/api/v1/users', [
                'tenant_id' => '11111111-1111-1111-1111-111111111111',
                'name'      => 'Novo Usuário',
                'email'     => 'newuser@acme.com',
                'role'      => 'agent',
                'password'  => 'Password!123',
            ])->assertStatus(201)
              ->assertJsonStructure(['data'=>['id']]);
    }
}
