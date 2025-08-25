# Architecture — Visão Macro da Solução

## Modelagem de Dados

### Users
- Campos principais: `id`, `tenant_id`, `name`, `email`, `password_hash`, `role`.
- Auditoria: `created_by`, `updated_by`, **`deleted_by`** (novo, para consistência com vehicles).
- Segurança: campos auxiliares recomendados:
  - `last_login_at` (último login bem-sucedido).
  - `failed_logins` (contador de falhas de login, resetado após sucesso).
  - **`last_password_change_at`** (data/hora da última troca de senha).

### Vehicles
- Campos principais: `id`, `tenant_id`, `brand`, `model`, `year`, `price`, `status`.
- Auditoria: `created_by`, `updated_by`, `deleted_by`.
- Imagens: **`images`** (array de URLs, alinhado com OpenAPI; máx. 10 imagens por veículo).
- Índices:
  - Existentes: `(brand)`, `(model)`, `(year)`, `(price)`.
  - **Sugerido:** índice parcial em `(status = 'available', price)` para buscas frequentes de veículos disponíveis até certo valor.

### Tenant_domains
- Permite domínio customizado por tenant.
- **Nota:** validação deve garantir exclusividade de domínio (um domínio só pode pertencer a um único tenant).

---

## Convenções de API

### Respostas de Sucesso
- Formato padrão (lista):
  ```json
  {
    "data": [...],
    "meta": {...},
    "links": {...}
  }
````

* Formato padrão (objeto único):

  ```json
  {
    "data": {...}
  }
  ```

### Respostas de Erro

* Formato consistente com ADR-03:

  ```json
  {
    "code": "ERROR_CODE",
    "message": "Descrição do erro",
    "details": {...}
  }
  ```
* Status codes:

  * `400` (Bad Request — validação).
  * `401` (Não autenticado).
  * `403` (Proibido — RBAC/tenant).
  * `404` (Não encontrado).
  * `429` (Rate-limit excedido — ver ADR-04).
  * `500` (Erro interno).

---

## Integrações Externas

* **S3 (Object Storage):** armazenamento de imagens de veículos; uso de URLs assinadas em uploads sensíveis.
* **SES/SMTP (Email/Notificação):** envio de emails de reset de senha; possibilidade futura de integração com fila (SQS).
* **Observabilidade:** logs estruturados, métricas e alarmes via CloudWatch; evolução futura para ELK/OpenTelemetry.
* **Redis (obrigatório em produção):**

  * Rate-limit segmentado (ADR-04).
  * Cache de resoluções de tenant e sessões.
  * Futuro: filas de jobs (ex.: envio de emails assíncronos).
  * Nota: opcional apenas em ambiente de testes/MVP; em produção, é **requisito**.

---

## Auditoria

* Todos os recursos versionados incluem campos:

  * `created_by`
  * `updated_by`
  * `deleted_by`
  * **`impersonated_by`** (quando superuser atua em nome de outro usuário).
* Eventos auditados:

  * Criação, atualização, exclusão.
  * Logins (sucesso e falha).
  * Reset de senha.
  * Mudanças de role.
  * Tentativas de acesso proibido (403).

