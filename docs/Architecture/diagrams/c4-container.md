# C4 — Nível 2 (Containers)

## Containers Principais

1. **React SPA (Browser)**
   - Build estático, roteamento client-side.
   - Consome `/api/v1`.
   - Gerencia sessão com **Sanctum Bearer Token (Authorization header)**.

2. **API Laravel**
   - Módulos: Auth, Tenant Resolver, RBAC (Policies), Vehicles, Errors/Pagination/Filters, Audit Logger, Rate-limit.
   - Middlewares: `auth:sanctum`, tenant resolver, throttle, correlation-id, enforce-json, CORS.
   - Logs estruturados e correlação de requisições.

3. **RDS**
   - Tabelas com `tenant_id`, constraints compostas, índices para queries.

4. **S3**
   - Buckets com versionamento; políticas por prefixo de tenant.

5. **Redis**
   - Rate-limit, cache de tenant e sessões, filas.
   - Opcional em CI/MVP; obrigatório em produção.

6. **Email (SES/SMTP)**
   - Envio de reset de senha.
   - Futuro: envio assíncrono via SQS.

7. **Observabilidade**
   - CloudWatch (logs/métricas), alarmes 5xx, p95 latência, endpoint `/health`.
   - Futuro: tracing via OpenTelemetry.

## Relações
SPA → API (HTTPS, JSON)  
API ↔ Redis (cache/rate-limit/jobs)  
API ↔ RDS (SQL)  
API ↔ S3 (SDK)  
API → SES/SMTP (emails)  
API → Observabilidade (logs/métricas/tracing)
