# Catálogo de Erros — API

## Objetivo
Unificar todos os `code` de erro, seus status HTTP e onde ocorrem.  
O envelope de erro é sempre:

```json
{
  "code": "ERROR_CODE",
  "message": "Mensagem curta e clara.",
  "errors": { "campo": ["mensagem detalhada"] } // opcional, usado em validações
}
````

---

## Tabela consolidada

| **Code**                | **HTTP** | **Message (exemplo)**                             | **Onde ocorre**                                                                                  |
| ----------------------- | -------- | ------------------------------------------------- | ------------------------------------------------------------------------------------------------ |
| `BAD_REQUEST`           | 400      | "Requisição malformada ou parâmetros inválidos."  | Header obrigatório ausente (ex.: `X-Tenant` em dev/admin), query param inválido, JSON malformado |
| `UNAUTHENTICATED`       | 401      | "Sessão inválida ou token ausente/expirado."      | Header Authorization: Bearer <token> ausente, inválido ou expirado                               |
| `FORBIDDEN`             | 403      | "Acesso negado."                                  | Policies RBAC (usuário autenticado mas sem permissão no recurso/tenant)                          |
| `NOT_FOUND`             | 404      | "Recurso não encontrado."                         | ID inexistente **ou cross-tenant** (não vazar existência)                                        |
| `CONFLICT`              | 409      | "Conflito de dados."                              | Unique constraint (ex.: placa duplicada em um tenant)                                            |
| `VALIDATION_ERROR`      | 422      | "Campos inválidos."                               | Form Requests (ex.: POST/PUT vehicles, login com formato inválido)                               |
| `RATE_LIMIT_EXCEEDED`   | 429      | "Muitas requisições. Tente novamente mais tarde." | Throttle/rate-limit (auth, rotas públicas, por IP/tenant/user)                                   |
| `INTERNAL_SERVER_ERROR` | 500      | "Erro inesperado. Tente novamente mais tarde."    | Exceptions não tratadas, falhas inesperadas                                                      |
| `SERVICE_UNAVAILABLE`   | 503      | "Serviço temporariamente indisponível."           | Manutenções planejadas ou indisponibilidade temporária do backend                                |

---

## Exemplos por caso

### 400 — BAD\_REQUEST

```json
{
  "code": "BAD_REQUEST",
  "message": "Requisição malformada ou parâmetros inválidos."
}
```

### 401 — UNAUTHENTICATED

```json
{
  "code": "UNAUTHENTICATED",
  "message": "Sessão inválida ou token ausente/expirado."
}
```

### 403 — FORBIDDEN

```json
{
  "code": "FORBIDDEN",
  "message": "Acesso negado."
}
```

### 404 — NOT\_FOUND

```json
{
  "code": "NOT_FOUND",
  "message": "Recurso não encontrado."
}
```

> Observação: usado tanto para recurso inexistente quanto para cross-tenant (para não vazar informação).

### 409 — CONFLICT

```json
{
  "code": "CONFLICT",
  "message": "Já existe veículo com esta placa neste tenant."
}
```

### 422 — VALIDATION\_ERROR

```json
{
  "code": "VALIDATION_ERROR",
  "message": "Campos inválidos.",
  "errors": {
    "email": ["Formato inválido"],
    "plate": ["Já em uso"]
  }
}
```

### 429 — RATE\_LIMIT\_EXCEEDED

Headers adicionais:

```
Retry-After: 60
```

```json
{
  "code": "RATE_LIMIT_EXCEEDED",
  "message": "Muitas requisições. Tente novamente mais tarde."
}
```

### 500 — INTERNAL\_SERVER\_ERROR

```json
{
  "code": "INTERNAL_SERVER_ERROR",
  "message": "Erro inesperado. Tente novamente mais tarde."
}
```

---

## Regras de uso

* **Code é estável**: nunca muda entre releases da mesma major.
* **Message** é curta, voltada ao usuário final.
* **Errors** (objeto) aparece em `422` e pode ser usado em outros para debug.
* Logs sempre registram `request_id`, `tenant_id`, `user_id` para correlação.
* **429** deve sempre conter `Retry-After`.
* **404** deve ser usado também para cross-tenant.
* **500** sempre genérico (não expor stack trace).
* **503** reservado para manutenções planejadas (opcional futuro).

---

## Referências

* ADR-03 (padrões de API e erros JSON)
* ADR-04 (rate-limit)
* OpenAPI.yaml (exemplos por rota)

