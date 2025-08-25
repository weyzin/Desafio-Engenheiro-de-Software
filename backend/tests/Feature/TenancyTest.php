<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TenancyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
        $this->seed();
    }

    public function test_requires_tenant_header_in_dev_when_no_domain_resolution(): void
    {
        // Sem X-Tenant em dev → 400 TENANT_HEADER_REQUIRED
        $res = $this->getJson('/api/v1/me');
        $res->assertStatus(400)->assertJson(['code' => 'TENANT_HEADER_REQUIRED']);
    }

    public function test_cross_tenant_returns_404(): void
    {
        // login como owner da ACME
        $headersAcme = ['X-Tenant' => 'acme', 'Accept' => 'application/json'];
        $this->withHeaders($headersAcme)->postJson('/api/v1/auth/login', [
            'email' => 'owner@acme.com', 'password' => 'Password!123',
        ])->assertStatus(200);

        // pegar um vehicle da GLOBEX (id conhecido pelo seed retorna 404 pois escopo filtra)
        $headersGlobex = ['X-Tenant' => 'globex', 'Accept' => 'application/json'];
        $vehGlobex = $this->withHeaders($headersGlobex)
            ->get('/api/v1/vehicles')->json('data.0.id');

        // agora tentar acessar esse id estando no tenant ACME → 404
        $this->withHeaders($headersAcme)
            ->get("/api/v1/vehicles/{$vehGlobex}")
            ->assertStatus(404)
            ->assertJson(['code' => 'NOT_FOUND']);
    }
}
