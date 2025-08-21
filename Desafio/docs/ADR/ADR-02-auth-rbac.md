# ADR-02: Autenticação (Sanctum SPA) e Autorização (Policies) com papéis

**Contexto**  
Precisamos autenticar uma SPA e aplicar autorização baseada em papéis: `superuser` (global), `owner` (tenant), `agent` (operacional).

**Opções consideradas**  
1) **Laravel Sanctum** (SPA com cookies HttpOnly, CSRF).  
2) Laravel Passport (OAuth2/JWT).  
3) JWT manual (bibliotecas externas).

**Decisão**  
Usar **Sanctum** para **SPA**, com cookies **HttpOnly** e proteção **CSRF** (XSRF-TOKEN).  
Armazenar papéis diretamente em uma **coluna `role`** no usuário (`superuser`, `owner`, `agent`), suficiente para o escopo atual do MVP.  
Autorizar via **Policies** (por recurso) e **Gates** (por papéis/ações).  
 Todo acesso **cross-tenant** de superuser deve ser **auditado e restrito**, conforme definido no ADR-01.

**Consequências (prós/cons, dívidas)**  
* **Prós**  
  * Simples para SPA; segura (cookies HttpOnly + SameSite).  
  * Policies integradas ao Eloquent; fácil teste e manutenção.  
* **Contras**  
  * Não é OAuth2 completo (casos B2B/futuros integrações podem exigir Passport).  
* **Dívidas**  
  * Opcional **2FA** opt-in por tenant (tenant decide ativar ou não para seus usuários).  
  * **Refresh de sessão** estendido, se exigido futuramente.  
  * **Expiração de sessão configurável**, diferenciando:  
    * **Expiração curta** para rotas sensíveis (ex.: `auth`, `users`).  
    * **Expiração padrão** para rotas comuns.  
  * **Revogação manual de tokens** para maior controle de segurança.  

**Implementação/Notas**  
* Endpoints:  
  * `POST /api/v1/auth/login`  
  * `POST /api/v1/auth/logout` (invalidação de sessão/token)  
  * `POST /api/v1/auth/forgot` (fluxo de recuperação de senha deve respeitar `tenant_id`: owner/agent só podem resetar dentro do próprio tenant)  
  * `GET /api/v1/me`  
* Headers de segurança: `X-Frame-Options`, `X-Content-Type-Options`, CSP básica, `Strict-Transport-Security`.  
* CORS restritivo ao domínio do SPA.  
* Policies:  
  * **Vehicles** (viewAny, view, create, update, delete) com checks de `tenant_id` e `role`.  
  * **Users**: apenas Owners podem CRUD de usuários dentro do tenant; superuser apenas em endpoints administrativos auditados.  
* **Testes**: validar login/logout, fluxo de recuperação de senha por tenant, e bloqueio de ações fora do papel.  

**Referências**  
PDF — RBAC/Autenticação; Laravel Docs (Sanctum, Policies/Gates).
