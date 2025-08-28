# Guia prático de Tenancy

## Conceito
Cada recurso pertence a um **tenant**.  
Toda requisição da SPA → API deve ser filtrada por `tenant_id`.  
Exceção: **superusers** podem acessar múltiplos tenants, mas **todo acesso cross-tenant é auditado** (vide ADR-01).

---

## Resolução de Tenant
1. **Ambiente dev/test:**  
   - Usar header `X-Tenant` para selecionar tenant explicitamente.  
2. **Produção:**  
   - Se não houver header, assume-se o `tenant_id` do usuário autenticado.  
   - Subdomínios/domínios customizados (via `tenant_domains`) são **plano futuro**, não implementados ainda.  
3. **Middleware TenantResolver:**  
   - Resolve tenant conforme regra acima.  
   - Injeta contexto para Global Scope.  
   - Se tenant não encontrado: `404 NOT_FOUND`.  

---

## Global Scope + Policies
- Todas as queries Eloquent incluem `where tenant_id = ?`.  
- **Policies** reforçam escopo: Owner/Agent limitados ao próprio tenant.  
- **Superuser cross-tenant** permitido só em endpoints administrativos e auditado.

---

## Auditoria
- Campos: `created_by`, `updated_by`, `deleted_by`, `impersonated_by`.  
- Logs estruturados em JSON incluem tentativas proibidas (`403`) e acessos cross-tenant.  

---

## Erros comuns
- `X-Tenant` inválido (dev/test): **400 BAD_REQUEST**.  
- Tenant não encontrado: **404 NOT_FOUND**.  
- Usuário de outro tenant acessando recurso: **404 NOT_FOUND**.  

---

## Constraints e Consistência
- `unique(tenant_id, plate)` em `vehicles`.  
- `unique(email, tenant_id)` em `users`.  
- Fallback para superuser (`tenant_id IS NULL`).  

---

## Testes recomendados
- Dev/test: `X-Tenant` válido → 200; ausente → 400.  
- Produção: sem header → fallback para usuário autenticado.  
- Superuser cross-tenant → 200 e evento de auditoria.  
- Header em produção (fora de whitelist admin) → bloqueado.  

---

## Impersonação
- Apenas superuser em rotas administrativas.  
- Gravar `impersonated_by`/`impersonated_at`.  
- Auditar início/fim da sessão.  

---

## Pseudocódigo (simplificado)

```php
if (app()->env(['local','testing']) && $request->hasHeader('X-Tenant')) {
  $tenant = Tenant::find($request->header('X-Tenant'));
} else {
  $tenant = $request->user()->tenant;
}
abort_if(!$tenant, 404);

app()->instance(CurrentTenant::class, $tenant);
