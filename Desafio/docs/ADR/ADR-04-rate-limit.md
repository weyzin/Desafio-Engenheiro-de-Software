# ADR-04: Rate-limit segmentado por rota e identidade

**Contexto**  
Reduzir abuso e proteger recursos: rotas públicas de auth devem ser mais restritas; rotas autenticadas equilibradas para UX.

**Opções consideradas**  
1) RateLimiter (Laravel) com **chaves por IP/tenant/usuário**; Redis como store.  
2) Limitador global único.  
3) Solução externa (API Gateway/WAF) — fora do escopo de execução.

**Decisão**  
Usar **Laravel RateLimiter** com políticas distintas:  
- `/api/v1/auth/*`: **5 req/min** por **IP** (parametrizável via **ENV**).  
- Rotas autenticadas: **60 req/min** por **(tenant_id, user_id)**, com bursts curtos permitidos (ex.: até **10 req em 5s**).  
- Fallback para **30 req/min** por **IP** em rotas públicas restantes.  

Chaves incluem `tenant_id` quando disponível para melhor isolamento.  
Store recomendado: **Redis**.  
Todos os limites (auth, autenticadas, fallback) devem ser **configuráveis via ENV**, não hardcoded.

**Consequências (prós/cons, dívidas)**  
+ Simples, integrado ao framework; reduz brute force e abuso.  
+ Segmentação por identidade/tenant evita interferência entre clientes.  
− Requer Redis para melhor precisão e distribuição.  
Dívidas:  
- Integração futura com **WAF/API Gateway** para limites por ASN/Geo.  
- Listas de permissões/bloqueios.  
- Limites dinâmicos baseados em **roles/planos de tenant** (ex.: premium vs free).
- Limites adaptativos por comportamento anômalo (ex.: spikes)

**Implementação/Notas**  
- Definições no `RouteServiceProvider` usando `RateLimiter::for(...)`.  
- Retornos **429** com corpo consistente definido no ADR-03:  
```json
{ "code": "RATE_LIMIT_EXCEEDED", "message": "Muitas requisições. Tente novamente mais tarde." }
````

* Métricas: contagem de 429 por rota e p95 de latência sob carga.
* Número de requisições bloqueadas por IP/tenant
* **Testes recomendados**:

  * Validar limites diferenciados para auth e rotas autenticadas.
  * Simular excesso de requisições para garantir retorno **429** com payload padrão.

**Referências**
PDF — Rate-limit; Laravel Docs (Rate Limiting).
