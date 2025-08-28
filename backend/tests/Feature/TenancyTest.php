<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TenancyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
        $this->seed();
    }

    public function test_without_header_falls_back_to_user_tenant_and_lists_ok(): void
    {
        // Login como agent@acme com header (só para obter token)
        $login = $this->withHeaders(['X-Tenant'=>'acme','Accept'=>'application/json'])
            ->postJson('/api/v1/auth/login', ['email'=>'agent@acme.com','password'=>'Password!123'])
            ->assertStatus(200)->json('data');

        $token = $login['token'];

        // Acessa rota com middleware `tenant` SEM X-Tenant → controlador usa user()->tenant_id
        $this->withHeaders([
                'Accept'        => 'application/json',
                'Authorization' => "Bearer {$token}",
            ])->get('/api/v1/vehicles')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['id','brand','model','year','price','status']],
                'meta' => ['total','page','per_page','last_page'],
            ]);
    }

    public function test_cross_tenant_returns_forbidden_or_not_found(): void
    {
        // 1) pega id de veículo da GLOBEX
        $globex = $this->withHeaders(['X-Tenant'=>'globex','Accept'=>'application/json'])
            ->postJson('/api/v1/auth/login', ['email'=>'owner@globex.com','password'=>'Password!123'])
            ->assertStatus(200)->json('data');

        $vehGlobexId = $this->withHeaders([
                'X-Tenant'      => 'globex',
                'Accept'        => 'application/json',
                'Authorization' => "Bearer {$globex['token']}",
            ])->get('/api/v1/vehicles?per_page=1')
            ->assertStatus(200)
            ->json('data.0.id');

        // 2) loga na ACME e tenta acessar o veículo da GLOBEX
        $acme = $this->withHeaders(['X-Tenant'=>'acme','Accept'=>'application/json'])
            ->postJson('/api/v1/auth/login', ['email'=>'owner@acme.com','password'=>'Password!123'])
            ->assertStatus(200)->json('data');

        $resp = $this->withHeaders([
                'X-Tenant'      => 'acme',
                'Accept'        => 'application/json',
                'Authorization' => "Bearer {$acme['token']}",
            ])->get("/api/v1/vehicles/{$vehGlobexId}");

        // Alguns fluxos dão 404 (query escopada), outros 403 (policy). Aceitamos ambos.
        $this->assertTrue(in_array($resp->getStatusCode(), [403,404], true), 'Esperado 403 ou 404 em cross-tenant');
    }
}
