# Guia prático de Tenancy

## Conceito
Cada recurso pertence a um **tenant** (ex.: empresa cliente).  
Toda requisição da SPA → API deve ser filtrada por `tenant_id`.  
Exceção: **superusers** podem acessar múltiplos tenants, mas **todo acesso cross-tenant é auditado** (vide ADR-01).

---

## Resolução de Tenant
1. **Domínios personalizados (`tenant_domains`)** — *case-insensitive*:  
   - (1) **Domínio customizado** → `tenant_id` alvo (match em `LOWER(domain)`).  
   - (2) **Subdomínio padrão** (ex.: `acme.app.com`) → `acme`.  
   - (3) **Fallback `X-Tenant`** (*apenas dev/admin*).  
   - Se nada resolver: **404 NOT_FOUND**.
2. **Produção vs. dev/test**  
   - **Produção**: o header **`X-Tenant` é bloqueado por padrão**; apenas **endpoints administrativos em whitelist** podem aceitá-lo e **sempre com auditoria**.  
   - **Dev/Test**: `X-Tenant` pode ser usado para facilitar desenvolvimento e testes.
3. **Normalização de domínio**  
   - `tenant_domains` é **case-insensitive** (normalizado para minúsculas) e único por tenant.
4. Middleware **`TenantResolver`**:
   - Extrai `tenant_id` via ordem acima.
   - Injeta contexto para **Global Scope** do Eloquent.
   - Retorna **404 NOT_FOUND** quando não encontrar tenant ou quando o recurso for de outro tenant (não vazar existência).

---

## Escopo Global (Global Scope) + Policies
- Todas as queries Eloquent incluem `where tenant_id = ?`.  
- **Policies** reforçam o escopo: Owner/Agent limitados ao próprio tenant; Superuser **pode atravessar tenants** apenas em **rotas/admin autorizadas**.  
- A dupla garantia (**Global Scope + Policies**) reduz risco de vazamento entre tenants.

---

## Auditoria
- Campos: `created_by`, `updated_by`, `deleted_by`, `impersonated_by` (quando aplicável).  
- Tentativas proibidas (**403 Forbidden**) e acessos cross-tenant são logados.  
- **Formato de log padronizado (JSON)**:
```json
{
  "event": "cross_tenant_access",
  "request_id": "uuid",
  "superuser_id": 1,
  "target_tenant": "globex",
  "actor_tenant": null,
  "route": "GET /api/v1/vehicles",
  "status": 200,
  "timestamp": "2025-08-20T12:00:00Z"
}
````

---

## Mensagens de falha usuais (DX)

* `X-Tenant` ausente (em dev/admin): **400 BAD\_REQUEST**

  ```json
  { "code": "TENANT_HEADER_REQUIRED", "message": "X-Tenant ausente em ambiente de desenvolvimento." }
  ```
* Tenant não encontrado (subdomínio/domínio customizado): **404 NOT\_FOUND**

  ```json
  { "code": "NOT_FOUND", "message": "Tenant inexistente." }
  ```
* Domínio reconhecido mas **usuário de outro tenant**: **404 NOT\_FOUND** (não vazar existência).

---

## Consistência de constraints (DDL/OpenAPI)

* **Unicidades por tenant**:

  * `unique(tenant_id, plate)` em `vehicles` (se `plate` existir).
  * **`unique(LOWER(domain))`** em `tenant_domains` (case-insensitive).
  * **Email único por tenant** (ou **global** para superuser `tenant_id IS NULL`).
* **Imposição de escopo**: todas as buscas (`Vehicles`, `Users`) herdam `tenant_id` por **Global Scope** e **Policies** (dupla garantia).
* **Coerência API ↔ DB**: API usa `images` (array); DB persiste `images_json` (mapper converte).

---

## Testes recomendados (dev/CI)

* ✅ **Resolução por subdomínio válido** → `200` em `/vehicles`.
* ✅ **Subdomínio inválido** → `404 NOT_FOUND`.
* ✅ **`X-Tenant` válido em dev** → `200`; ausente → **400 TENANT\_HEADER\_REQUIRED**.
* ✅ **Acesso a recurso de outro tenant (Owner/Agent)** → `404`.
* ✅ **Superuser acessando outro tenant** → `200` **e** registro de `cross_tenant_access` no log.
* ✅ **`tenant_domains` com case diferente (Foo.com)** → resolve para o **mesmo** tenant (normalização OK).
* ✅ **Header `X-Tenant` bloqueado em produção** (exceto endpoints admin em whitelist).

---

## Impersonação (operação administrativa)

* Recurso **administrativo** (rota/área `/admin/...`) e **apenas superuser**.
* Ao iniciar impersonação:

  * Gravar `impersonated_by` e `impersonated_at`.
  * Limitar escopo **sempre** ao tenant alvo (evita “super poderes” irrestritos).
  * Operação restrita e com log obrigatório.
* **Auditar** início/fim da sessão de impersonação com `request_id`, `superuser_id`, `target_tenant`.
* Encerrar impersonação deve restaurar o contexto original e registrar no log.

---

## Pseudocódigo (exemplo simplificado)

// TenantResolver (ordem)

```php
if ($tenant = matchCustomDomain($host)) return $tenant;
if ($tenant = matchSubdomain($host))   return $tenant;
if (isDevOrAdmin() && hasXTenant())    return $tenant;
throw NotFoundException(); // 404
```

// Middleware TenantResolver

```php
$tenant = resolveByCustomDomainOrSubdomain($request->host());
if (!$tenant && app()->env(['local','testing']) && $request->hasHeader('X-Tenant')) {
  $tenant = Tenant::where('id', $request->header('X-Tenant'))->first();
}
abort_if(!$tenant, 404);

app()->instance(CurrentTenant::class, $tenant);

// Global Scope
Model::addGlobalScope('tenant', fn($q) => $q->where('tenant_id', currentTenant()->id));

// Policy (ex.: VehiclePolicy@view)
return $user->isSuperuser()
  ? $this->isAdminRoute($request) // apenas admin pode cruzar tenants
  : $vehicle->tenant_id === $user->tenant_id;
```
