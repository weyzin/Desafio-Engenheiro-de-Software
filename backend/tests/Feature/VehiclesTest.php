<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;


class VehiclesTest extends TestCase
{
    use RefreshDatabase;

    protected array $h;
    protected string $token;

    protected function setUp(): void {
        parent::setUp();

        // Torna as requisições da suite stateless
        $this->withoutMiddleware([
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
        ]);

        // Nos testes desta classe, desabilita o modo stateful do Sanctum
        config()->set('sanctum.stateful', []);

        $this->seed();

        // login como agent@acme.com e guarda o Bearer
        $login = $this->withHeaders(['X-Tenant' => 'acme','Accept'=>'application/json'])
            ->postJson('/api/v1/auth/login', [
                'email' => 'agent@acme.com',
                'password' => 'Password!123',
            ])->assertStatus(200)->json('data');

        $this->h = [
            'X-Tenant'      => 'acme',
            'Accept'        => 'application/json',
            'Authorization' => "Bearer {$login['token']}",
        ];
    }

    public function test_list_with_filters_and_pagination(): void
    {
        $this->withHeaders($this->h)
            ->get('/api/v1/vehicles?brand=Toyota&price_min=20000&price_max=200000&page=2&per_page=10&sort=price,-year')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['id','brand','model','year','price','status']],
                'meta' => ['total','page','per_page','last_page'],
                'links' => ['next','prev'],
            ]);
    }

    public function test_crud_create_show_update(): void
    {
        // create
        $payload = [
            'brand'   => 'Ford',
            'model'   => 'Focus',
            'version' => 'SE 2.0',   // campo do seu schema
            'year'    => 2020,
            'km'      => 45000,
            'price'   => 45000,
            'images'  => ['https://cdn.example.com/focus1.jpg'],
            'status'  => 'available',
        ];
        $create = $this->withHeaders($this->h)->postJson('/api/v1/vehicles', $payload)
            ->assertStatus(201)
            ->assertJsonStructure(['data'=>['id']])
            ->json('data');

        $id = $create['id'];

        // show
        $this->withHeaders($this->h)
            ->get("/api/v1/vehicles/{$id}")
            ->assertStatus(200)
            ->assertJsonPath('data.brand','Ford')
            ->assertJsonPath('data.version','SE 2.0')
            ->assertJsonPath('data.km', 45000);

        // update
        $this->withHeaders($this->h)
            ->putJson("/api/v1/vehicles/{$id}", ['price'=>47000])
            ->assertStatus(200)
            ->assertJsonPath('data.price', 47000.0);
    }

    public function test_validation_422(): void
    {
        $this->withHeaders($this->h)
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
