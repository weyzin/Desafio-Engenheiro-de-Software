# ADR-01: Tenancy em single-DB com `tenant_id`

**Contexto**  
O sistema deve isolar dados por organização. O escopo do desafio favorece simplicidade operacional e baixo custo, sem necessidade de sharding ou múltiplos bancos por tenant.

**Opções consideradas**  
1) Single-DB, coluna `tenant_id` + Global Scopes/Policies.  
2) Multi-DB (um banco por tenant).  
3) Schema-per-tenant (mesmo DB, schemas distintos).

**Decisão**  
Adotar **single-DB** com coluna `tenant_id` em entidades multitenant (e.g., `users`, `vehicles`). Aplicar **Global Scope** no Eloquent e **Policies** para reforço na camada de autorização. Resolver tenant por **subdomínio** (ex.: `acme.app.com`) com **fallback header `X-Tenant`**.  
👉 O header `X-Tenant` será aceito apenas em **cenários controlados** (dev/test/admin). Em **produção**, deve ser **bloqueado por padrão**, exceto em uma whitelist explícita de endpoints administrativos, sempre com forte validação para evitar spoofing.

**Consequências (prós/cons, dívidas)**  
* **Prós**  
  * Simplicidade de operação, custo baixo, migrações únicas.  
  * Facilidade para relatórios cross-tenant (via superuser).  
* **Contras**  
  * Menor isolamento físico (risco mitigado por Policies, testes e validação).  
  * Necessidade de **constraints compostas** (e.g., `unique(tenant_id, plate)` em veículos, `unique(tenant_id, email)` em usuários, etc.).  
* **Dívidas**  
  * Plano de evolução para múltiplos DBs caso crescimento exija (chave lógica permanece `tenant_id`).  
  * Migração futura pode **particionar dados por `tenant_id`**, evoluindo para múltiplos DBs ou sharding.  
  * A manutenção do `tenant_id` como chave lógica garante **compatibilidade retroativa**, mesmo após sharding ou migração para múltiplos DBs.

**Implementação/Notas**  
* Trait `BelongsToTenant`; Middleware `ResolveTenant` (subdomínio > header).  
* Índices por `tenant_id` + campos de filtro.  
* Campos de auditoria (`created_by`, `updated_by`, `deleted_by`) também vinculam `tenant_id`, impedindo ações cross-tenant.  
* Bypass de Global Scope para superuser quando estritamente necessário; **todo acesso cross-tenant deve ser auditado e restrito a endpoints administrativos**.  
* Testes:  
  * Impedir acesso cross-tenant (usuário comum).  
  * Validar comportamento do header `X-Tenant` inválido.  
  * Confirmar acesso positivo de superuser a múltiplos tenants (cross-tenant legítimo).  

**Referências**  
PDF — seção Multi-tenancy; Laravel Docs (Global Scopes, Policies).
