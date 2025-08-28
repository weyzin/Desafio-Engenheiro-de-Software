# ADR-01: Tenancy em Single-DB com `tenant_id`

## 1. Contexto
O sistema precisa garantir **isolamento de dados por organização (tenant)** de forma simples, com baixo custo e manutenção centralizada.  
Para o escopo atual, não é necessário sharding nem múltiplos bancos de dados.

## 2. Opções consideradas
1. **Single-DB com coluna `tenant_id`** em entidades multitenant, usando Global Scopes e Policies.  
2. Multi-DB (um banco por tenant).  
3. Schema-per-tenant (mesmo DB, múltiplos schemas).  

## 3. Decisão
Adotamos **Single-DB** com coluna `tenant_id` em entidades multitenant (e.g. `users`, `vehicles`).  
O tenant é resolvido da seguinte forma:

- **Ambiente de desenvolvimento/testes:** via header `X-Tenant`.  
- **Ambiente de produção:** fallback automático para o `tenant_id` do usuário autenticado, quando o header não é enviado.  
- **Cross-tenant:** bloqueado por padrão (retorna **403/404**). Apenas usuários `superuser` podem acessar múltiplos tenants em endpoints administrativos **auditados**.  

> 🔮 Futuro: está no roadmap permitir resolução de tenant por **subdomínio** (ex.: `acme.app.com`), mantendo compatibilidade retroativa com `tenant_id`.

## 4. Consequências
### Prós
- Simplicidade operacional e custo baixo.  
- Migrations únicas.  
- Facilidade para relatórios cross-tenant via superuser.  

### Contras
- Menor isolamento físico entre tenants.  
- Risco de spoofing de header se não validado em ambientes restritos.  

### Dívidas Técnicas
- Evolução futura para múltiplos DBs ou sharding caso necessário.  
- `tenant_id` mantido como chave lógica garante compatibilidade retroativa.  

## 5. Implementação / Notas
- Trait **`BelongsToTenant`** aplicada a modelos.  
- Middleware **`ResolveTenant`**:  
  - Primeiro tenta **X-Tenant** (em dev/test).  
  - Fallback: usa o `tenant_id` do usuário autenticado.  
- **Constraints compostas** em campos críticos:  
  - `unique(tenant_id, email)` em usuários.  
  - `unique(tenant_id, plate)` em veículos.  
- Auditoria: campos (`created_by`, `updated_by`, `deleted_by`) vinculados a `tenant_id`.  
- Superuser pode fazer bypass de Global Scopes somente em endpoints administrativos auditados.  
- Testes automatizados garantem:  
  - Bloqueio de acessos cross-tenant indevidos.  
  - Validação de headers inválidos.  
  - Acesso legítimo de superuser.  

## 6. Referências
- Laravel Docs: Global Scopes, Policies.  
- [Multi-Tenancy Patterns](https://learn.microsoft.com/en-us/azure/architecture/guide/multitenant/overview).
