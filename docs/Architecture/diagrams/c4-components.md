# C4 — Context (Nível 1)

## Pessoas
- **Owner/Agent (usuário do painel):** acessa o SPA React para operar veículos do seu tenant.
- **Superuser (equipe interna):** usa o mesmo SPA com privilégios administrativos e cross-tenant.

## Sistemas
- **Painel Web (React SPA):** frontend que consome a API e apresenta telas de login, listagens e CRUD.
  - Resolução de tenant: `X-Tenant` (dev/test) ou fallback para tenant do usuário (prod).
- **API Laravel:** backend REST `/api/v1`, multitenant, RBAC, auditoria, rate-limit.

## Sistemas Externos / Infra
- **RDS:** persistência de tenants, usuários, veículos e auditoria.
- **S3:** imagens de veículos.
- **SES/SMTP:** envio de emails (reset de senha).
- **Redis:** rate-limit, cache de tenants e filas.
- **Observabilidade:** CloudWatch (logs, métricas, alarmes); futuro: OpenTelemetry/ELK.

## Relações
Usuário → SPA (HTTPS)  
SPA → API (HTTPS, JSON)  
API → RDS (SQL)  
API → S3 (upload/listing)  
API → SES/SMTP (emails)  
API ↔ Redis (cache, rate-limit, jobs)  
API → Observabilidade (logs, métricas, tracing)
