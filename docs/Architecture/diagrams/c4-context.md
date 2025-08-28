# C4 — Nível 3 (Componentes dentro da API Laravel)

## Componentes

### HTTP Layer
- Controllers: `AuthController`, `Controller`, `HealthController`, `TenantController`, `UserController`, `VehiclesController`.
- Middlewares: `TenantResolver` (X-Tenant → fallback user), `RequestId`, `EnforceJson`, `CORS`, `Throttle`. Os três últimos devem ser implementados antes de subir em Produção, ainda não existem no MVP.
- Controllers apenas orquestram chamadas; regras de negócio ficam nos Services. Regras de Negócio devem ser movidas para os Services antes de subir em Produção, ainda estão nos Controllers no MVP.

### Auth & RBAC
- **Sanctum (API Tokens, Bearer, stateless).**
- Fluxo de Password Reset:
  - Email via SES/SMTP.
  - Token temporário no DB.
  - Validação na troca de senha (`/auth/reset`).
- Policies (VehiclePolicy, UserPolicy) + Gates para roles (`superuser`, `owner`, `agent`).
- Permissões:
  - Vehicles: create/update/view = owner/agent; delete = owner/superuser (auditado).
  - Users: CRUD = superuser; 
  - Tenants: CRUD = superuser; 

### Domain: Vehicles
- Services: `VehicleService` (regras, auditoria), `ImageService` (upload S3).
- ORM: Eloquent + Global Scope `tenant_id`.
- Uploads: formatos suportados (jpg/png), limite de tamanho configurável, URLs assinadas.

### Cross-cutting
- Validation: Form Requests.
- Pagination/Filters: utilitário unificado, resposta padrão ADR-03.
- Transformers/Resources (e.g. VehicleResource).
- Error Handler: `{code, message, details}` (ADR-03).
- Audit Logger: events/listeners; persistência `created_by/updated_by/deleted_by`.
- Rate Limiter: segmentado, chave `(tenant_id, user_id, IP)` (ADR-04).
- Observabilidade: Monolog JSON com `request_id`, `tenant_id`, `user_id`, `route`, `status`, `latency_ms`.

## Fluxos principais
- Login: `AuthController` → Sanctum (Bearer) → token → `AuthController`.
- CRUD Vehicles: Controller → Policy → Service → Eloquent (tenant scope) → RDS → Audit → Logs.
- Upload imagem: Controller → Policy → `ImageService` → S3 → URL pública/assinada.
