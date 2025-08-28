# Architecture — Visão Macro da Solução

## 1. Modelagem de Dados

### Users
- Campos principais: `id`, `tenant_id`, `name`, `email`, `password_hash`, `role`.
- Auditoria: `created_by`, `updated_by`, `deleted_by`.
- Segurança:
  - `last_login_at` (último login bem-sucedido).
  - `failed_logins` (contador de falhas de login, resetado após sucesso).
  - `last_password_change_at` (data/hora da última troca de senha).

### Vehicles
- Campos principais: `id`, `tenant_id`, `brand`, `model`, `year`, `price`, `status`.
- Auditoria: `created_by`, `updated_by`, `deleted_by`.
- Imagens: `images` (array de URLs, máx. 10 imagens por veículo).
- Índices:
  - `(brand)`, `(model)`, `(year)`, `(price)`.
  - Índice parcial sugerido: `(status = 'available', price)`.

### Tenant resolution
- **Ambiente dev/test:** `X-Tenant` (header).
- **Produção:** fallback automático para `tenant_id` do usuário autenticado quando o header não vem.
- **Futuro:** possibilidade de uso de subdomínios customizados via tabela `tenant_domains`.
  - Exclusividade de domínio garantida (um domínio pertence a um único tenant).

---

## 2. Convenções de API

### Respostas de Sucesso
- Listas:
  ```json
  { "data": [...], "meta": {...}, "links": {...} }
Objeto único:

json
{ "data": {...} }
Respostas de Erro
Formato consistente (ADR-03):

json
{ "code": "ERROR_CODE", "message": "Descrição do erro", "details": {...} }
Status codes:

- 400 (validação), 401 (não autenticado), 403 (proibido RBAC/tenant),

- 404 (não encontrado), 429 (rate-limit excedido), 500 (erro interno).

## 3. Integrações Externas
- S3 (Object Storage): imagens de veículos; URLs assinadas em uploads sensíveis.

- SES/SMTP (Email): envio de emails de reset de senha. Futuro: envio assíncrono via SQS.

- Redis: obrigatório em produção.

- Rate-limit segmentado (ADR-04).

- Cache de resoluções de tenant.

- Fila de jobs (e.g. envio de emails assíncronos).

- Em CI/MVP: pode ser substituído por store em memória.

- Observabilidade: logs estruturados, métricas e alarmes via CloudWatch; evolução futura para ELK/OpenTelemetry.

## 4. Auditoria
- Campos: `created_by`, `updated_by`, `deleted_by`, `impersonated_by` (quando superuser atua por outro usuário).

- Eventos auditados:

- CRUD em entidades.

- Logins (sucesso/falha).

- Reset de senha.

- Mudança de role.

- Tentativas de acesso proibido (403).

