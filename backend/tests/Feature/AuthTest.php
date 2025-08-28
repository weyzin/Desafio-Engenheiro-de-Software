<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
        $this->seed();
    }

    public function test_sanctum_login_me_logout_flow(): void
    {
        $headers = ['X-Tenant' => 'acme', 'Accept' => 'application/json'];

        // login (retorna token + user)
        $login = $this->withHeaders($headers)->postJson('/api/v1/auth/login', [
            'email' => 'owner@acme.com',
            'password' => 'Password!123',
        ])->assertStatus(200)
          ->assertJsonStructure(['data' => ['token','token_type','user' => ['id','email','name','role','tenant_id']]])
          ->json('data');

        $token = $login['token'];

        // me (sem exigir tenant; protegido só por auth)
        $me = $this->withHeaders([
                'Accept'        => 'application/json',
                'Authorization' => "Bearer {$token}",
            ])->get('/api/v1/me')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['id','email','name','role','tenant_id','active_tenant']]);

        // logout (revoga o token atual)
        $this->withHeaders([
                'Accept'        => 'application/json',
                'Authorization' => "Bearer {$token}",
            ])->post('/api/v1/auth/logout')
            ->assertStatus(204);
    }

    public function test_forgot_is_idempotent_and_returns_200(): void
    {
        $this->withHeaders(['X-Tenant' => 'acme', 'Accept' => 'application/json'])
            ->postJson('/api/v1/auth/forgot', ['email' => 'owner@acme.com'])
            ->assertStatus(200)
            ->assertJson(fn($j) => $j->has('message'));
    }
}
