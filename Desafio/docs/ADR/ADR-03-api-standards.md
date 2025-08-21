# ADR-03: Padrões de API — REST, /api/v1, respostas JSON e paginação

**Contexto**  
A API deve ser previsível, versionada e com contratos claros (OpenAPI). Respostas de sucesso e erro precisam de formato consistente.  
Paginação e metadados devem seguir padrão único em todos os endpoints.

**Opções consideradas**  
1) RESTful com **/api/v1** + OpenAPI + envelope padrão.  
2) GraphQL (não pedido).  
3) REST sem versionamento explícito.

**Decisão**  
Adotar **REST** com prefixo **/api/v1**.  
Documentar contratos em **OpenAPI (YAML)**.  
Respeitar envelope consistente para **sucesso** e **erro**.

### Formato de resposta de sucesso

**Listas (coleções paginadas):**
```json
{
  "data": [
    { "id": 1, "name": "Foo" },
    { "id": 2, "name": "Bar" }
  ],
  "meta": { "total": 120, "page": 2, "per_page": 20, "last_page": 6 },
  "links": {
    "next": "https://api.example.com/api/v1/vehicles?page=3",
    "prev": "https://api.example.com/api/v1/vehicles?page=1"
  }
}
````

**Item único:**

```json
{
  "data": { "id": 1, "name": "Foo" }
}
```

### Formato de resposta de erro

**Validação:**

```json
{
  "code": "VALIDATION_ERROR",
  "message": "Campos inválidos.",
  "details": { "price": ["O preço deve ser >= 0"] }
}
```

**400 Bad Request**

```json
{ "code": "BAD_REQUEST", "message": "Requisição inválida.." }
```

**401 Unauthorized**

```json
{ "code": "UNAUTHORIZED", "message": "Não autenticado." }
```

**403 Forbidden**

```json
{ "code": "FORBIDDEN", "message": "Acesso negado." }
```

**404 Not Found**

```json
{ "code": "NOT_FOUND", "message": "Recurso não encontrado." }
```

**429 Too Many Requests**

```json
{ "code": "RATE_LIMIT_EXCEEDED", "message": "Muitas requisições. Tente novamente mais tarde." }
```

**500 Internal Server Error**

```json
{ "code": "INTERNAL_ERROR", "message": "Erro inesperado." }
```

---

### Paginação

Todos os endpoints que retornam listas devem incluir:

* `meta.total` (total de itens)
* `meta.page` (página atual)
* `meta.per_page` (tamanho da página)
* `meta.last_page` (opcional, total de páginas)
* `links.next` / `links.prev` (quando aplicável)

Este padrão deve ser **consistente em todos os endpoints**.

---

### Versionamento

* Versão atual: `/api/v1`.
* Futuras versões terão `/api/v2`, mantendo **backward compatibility** enquanto necessário.

---

### Headers de resposta

* `Content-Type: application/json` (padrão).
* `Cache-Control` em endpoints GET (ex.: `Cache-Control: public, max-age=60`).
* `X-Request-ID` em todas as respostas (para correlação de logs).

---

### HTTP Status Codes (boas práticas)

* **200 OK** — sucesso em GET.
* **201 Created** — sucesso em POST com criação de recurso.
* **204 No Content** — sucesso em DELETE.
* **400 Bad Request** — erro de validação.
* **401 Unauthorized** — não autenticado.
* **403 Forbidden** — sem permissão.
* **404 Not Found** — recurso inexistente.
* **429 Too Many Requests** — limite de requisições.
* **500 Internal Server Error** — erro inesperado.

---

### Consequências (prós/cons, dívidas)

**Prós**

* Consistência em todos os endpoints (sucesso/erro).
* Facilidade de integração (front e terceiros).
* Testes de contrato simples (Postman, Newman, etc.).
* Facilita evolução para testes automatizados de contrato

**Contras**

* Exige disciplina e validação em PRs.
* Precisa de padronização em **libs/middlewares** para evitar divergências manuais.

**Dívidas técnicas**

* Futuro suporte a formatos mais ricos (HAL, JSON\:API), mas manter o MVP simples e direto.

---

**Referências**
PDF — Padrões de API; Laravel API Resources; RFC 7807 (Problem+JSON).


