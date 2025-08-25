<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VehiclesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
        $this->seed();
        // login como agent@acme.com (pode CRUD)
        $this->withHeaders(['X-Tenant' => 'acme','Accept'=>'application/json'])
            ->postJson('/api/v1/auth/login', [
                'email' => 'agent@acme.com',
                'password' => 'Password!123',
            ])->assertStatus(200);
    }

    public function test_list_with_filters_and_pagination(): void
    {
        $res = $this->withHeaders(['X-Tenant' => 'acme'])
            ->get('/api/v1/vehicles?brand=Toyota&price_min=20000&price_max=200000&page=2&per_page=10&sort=price,-year');

        $res->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['id','brand','model','year','price','status','images']],
                'meta' => ['total','page','per_page','last_page'],
                'links' => ['next','prev'],
            ]);
    }

    public function test_crud_vehicle(): void
    {
        // create
        $payload = [
            'brand' => 'Ford',
            'model' => 'Focus',
            'year'  => 2020,
            'price' => 45000,
            'images'=> ['https://cdn.example.com/focus1.jpg'],
            'status'=> 'available',
        ];
        $res = $this->withHeaders(['X-Tenant' => 'acme'])
            ->postJson('/api/v1/vehicles', $payload);
        $res->assertStatus(201)->assertJsonStructure(['data'=>['id']]);
        $id = $res->json('data.id');

        // show
        $this->withHeaders(['X-Tenant' => 'acme'])
            ->get("/api/v1/vehicles/{$id}")
            ->assertStatus(200)
            ->assertJsonPath('data.brand','Ford');

        // update
        $this->withHeaders(['X-Tenant' => 'acme'])
            ->putJson("/api/v1/vehicles/{$id}", ['price'=>47000])
            ->assertStatus(200)
            ->assertJsonPath('data.price', 47000.0);

        // delete
        $this->withHeaders(['X-Tenant' => 'acme'])
            ->delete("/api/v1/vehicles/{$id}")
            ->assertStatus(204);
    }

    public function test_validation_422(): void
    {
        $this->withHeaders(['X-Tenant' => 'acme'])
            ->postJson('/api/v1/vehicles', [
                'brand' => 'Tesla',
                'model' => 'Model S',
                'year'  => 1800,  // inválido
                'price' => -10,   // inválido
            ])
            ->assertStatus(422)
            ->assertJson(['code' => 'VALIDATION_ERROR']);
    }
}
