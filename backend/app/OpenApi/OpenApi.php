<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    servers: [
        new OA\Server(
            url: "http://localhost:8080/api/v1",
            description: "Local Docker"
        ),
    ],
)]
#[OA\Info(
    version: "1.0.0",
    title: "Desafio – API",
    description: "Documentação da API (multi-tenant, usuários, veículos)."
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
)]
#[OA\Tag(name: "Auth", description: "Login e sessão")]
#[OA\Tag(name: "Profile", description: "Perfil do usuário")]
#[OA\Tag(name: "Tenants", description: "Gestão de tenants")]
#[OA\Tag(name: "Vehicles", description: "Gestão de veículos")]
class OpenApi
{
    // Esta classe fica vazia, serve só para conter os atributos acima.
}
