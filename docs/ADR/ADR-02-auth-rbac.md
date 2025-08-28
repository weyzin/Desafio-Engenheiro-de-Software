# ADR-02: Autenticação (Sanctum Bearer Token) e Autorização (RBAC)

## 1. Contexto
Precisamos autenticar uma SPA (frontend em React) contra o backend Laravel, usando autenticação segura e autorização baseada em papéis.  
Papéis suportados:  
- **superuser** (global, cross-tenant com auditoria).  
- **owner** (administrador do tenant).  
- **agent** (usuário operacional, permissões limitadas).  

## 2. Opções consideradas
1. **Laravel Sanctum** (API Tokens via Bearer, stateless).  
2. Laravel Passport (OAuth2/JWT).  
3. JWT manual (bibliotecas externas).  

## 3. Decisão
Adotamos **Laravel Sanctum em modo API Token (Bearer)**, operando de forma **stateless**.  
Tokens são enviados no header:

Authorization: Bearer <token>

A autorização segue **RBAC simples** via coluna `role` em `users`.  
Policies do Laravel reforçam o controle de acesso em nível de recurso.  

## 4. Consequências
### Prós
- Simples e seguro para SPA + API stateless.  
- Integração nativa com Laravel e testes.  
- Fácil extensão futura para OAuth2/Passport.  

### Contras
- Não suporta out-of-the-box fluxos complexos B2B (delegação, SSO).  

### Dívidas Técnicas
- Futuro **2FA** configurável por tenant.  
- Expiração diferenciada de tokens (curta para rotas sensíveis, padrão para demais).  
- **Revogação manual de tokens** para maior controle de segurança.  
- Possível evolução para OAuth2/Passport caso surjam integrações externas.  

## 5. Implementação / Notas
### Endpoints
- `POST /api/v1/auth/login` — autenticação via credenciais, gera token.  
- `POST /api/v1/auth/logout` — invalida token atual.  
- `POST /api/v1/auth/forgot` — inicia fluxo de recuperação de senha (scoped por tenant).  
- `POST /api/v1/auth/reset` — redefine senha, validando `tenant_id`.  
- `GET /api/v1/me` — retorna dados do usuário autenticado e seu `tenant_id`.  

### RBAC / Policies
- **Vehicles**:  
  - `create`, `update`, `view`: **owner** e **agent** (apenas dentro do tenant).  
  - `delete`: apenas **owner** ou **superuser** (auditado).  
- **Users**:  
  - CRUD permitido apenas a **superuser**, o user deve ser vinculado a um tenant.   

### Segurança adicional
- Headers de segurança:  
  - `Strict-Transport-Security`, `X-Frame-Options`, `X-Content-Type-Options`, CSP básica.  
- CORS restrito ao domínio do frontend.  
- Logs estruturados de eventos `auth.*` com `request_id` e `tenant_id`.  

### Testes automatizados
- Login/logout com Bearer Token.  
- Fluxo de recuperação de senha por tenant.  
- Acesso negado a usuários fora do papel.  
- Auditoria de acessos cross-tenant (superuser).  

## 6. Referências
- Laravel Docs: Sanctum (API Tokens), Policies e Gates.  
- OWASP Cheat Sheet Series — Authentication & Session Management.