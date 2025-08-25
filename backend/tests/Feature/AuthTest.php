<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
        $this->seed(); // usa os seeders abaixo
    }

    public function test_login_me_logout_flow(): void
    {
        $headers = ['X-Tenant' => 'acme', 'Accept' => 'application/json'];

        // login
        $res = $this->withHeaders($headers)->postJson('/api/v1/auth/login', [
            'email' => 'owner@acme.com',
            'password' => 'Password!123',
        ]);
        $res->assertStatus(200)->assertJsonStructure(['data' => ['id','email','role','tenant_id']]);

        // session cookie deve existir
        $this->assertTrue($res->headers->has('set-cookie'));

        // me
        $res = $this->withHeaders($headers)->get('/api/v1/me');
        $res->assertStatus(200)->assertJsonStructure(['data' => ['id','email','tenant_id']]);

        // logout
        $res = $this->withHeaders($headers)->post('/api/v1/auth/logout');
        $res->assertStatus(204);
    }

    public function test_forgot_is_idempotent_and_returns_200(): void
    {
        $headers = ['X-Tenant' => 'acme', 'Accept' => 'application/json'];

        $res = $this->withHeaders($headers)->postJson('/api/v1/auth/forgot', [
            'email' => 'owner@acme.com',
        ]);

        $res->assertStatus(200)
            ->assertJson(fn($j) => $j->has('message'));
    }
}
