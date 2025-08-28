# Checklist de Segurança (AppSec)

Lista prática para revisão antes de codar e antes de subir para produção.  
Cada item deve ser marcado quando atendido.  

---

## 🔐 Autenticação & Sessão
- [ ] **Senhas armazenadas com Argon2id** (padrão Laravel).
- [ ] **Sanctum Bearer Token (stateless)** como forma de autenticação (sem cookies/CSRF).
- [ ] **Expiração de token** configurada (curta em rotas sensíveis).
- [ ] **Idle timeout** de 30min de inatividade (requer refresh manual).
- [ ] **Revogação de token** em logout (invalidate token).
- [ ] **Tentativas de login** registradas (falhas e sucesso).
- [ ] **Alerta e bloqueio de brute force**: falhas consecutivas > N/minuto → alerta em SecOps → bloqueio por X minutos.
- [ ] **2FA opcional por tenant** planejado para evolução futura.

---

## 🏷️ Multitenancy
- [ ] **X-Tenant header** habilitado em dev/test.
- [ ] **Produção:** fallback para `tenant_id` do usuário autenticado quando não há header.
- [ ] **Subdomínios customizados** são plano futuro (tabela tenant_domains).
- [ ] **Global Scope** obrigatório em queries (tenant_id).
- [ ] **Superuser cross-tenant** auditado e restrito a endpoints administrativos.

---

## 🛡️ Validação de Inputs
- [ ] **Form Requests** em todas as rotas de escrita.
- [ ] **Limites explícitos** (ex.: imagens máx. 10 por veículo, formatos restritos).
- [ ] **Tamanho de strings** validado (nome máx. 255 chars, email máx. 254).
- [ ] **Campos obrigatórios** validados conforme OpenAPI.
- [ ] **Sanitização de strings** contra XSS/SQL injection.
- [ ] **Escape padrão** (`e()` no Blade, `escape: true` no React).

---

## 🚦 Rate-limit & Anti-abuso
- [ ] **Rate-limit segmentado** (auth, autenticadas, fallback público).
- [ ] **Chaves compostas** (IP, tenant_id, user_id).
- [ ] **Redis obrigatório em produção** para precisão.
- [ ] **Retorno 429** padronizado `{ code: "RATE_LIMIT_EXCEEDED", message }`.
- [ ] **Alerta se 429 > 10%** das requisições.

---

## 📜 Auditoria & Logs
- [ ] **Campos auditáveis**: created_by, updated_by, deleted_by, impersonated_by.
- [ ] **Tentativas proibidas (403)** registradas.
- [ ] **Logs estruturados JSON** com request_id, tenant_id, user_id.
- [ ] **Mascarar PII em logs** (emails, CPF, dados sensíveis).
- [ ] **Retenção segura** em CloudWatch/ELK (sem dados sensíveis no log).

---

## 🖼️ Uploads & Arquivos
- [ ] **Tipos de arquivo restritos** (JPEG/PNG).
- [ ] **Limite de tamanho** (ex.: máx. 5MB por imagem).
- [ ] **Varredura antivírus** (ClamAV ou AWS AV) — recomendação futura.
- [ ] **Armazenamento em S3** com políticas por prefixo de tenant.
- [ ] **URLs assinadas** para acesso temporário (quando aplicável).
- [ ] **Hash SHA256 armazenado em DB** para verificar integridade e antifraude.

---

## 🛡️ Cabeçalhos de Segurança
- [ ] `Content-Security-Policy: default-src 'self'`.
- [ ] `X-Content-Type-Options: nosniff`.
- [ ] `X-Frame-Options: DENY`.
- [ ] `Strict-Transport-Security: max-age=31536000; includeSubDomains`.
- [ ] `Referrer-Policy: no-referrer` ou `same-origin`.
- [ ] `Permissions-Policy` restritiva.
- [ ] `Cross-Origin-Resource-Policy: same-origin`.
- [ ] `Cross-Origin-Opener-Policy: same-origin`.

---

## 🔑 Gestão de Segredos
- [ ] **Nenhum segredo commitado** (usar `.env` + Secret Manager/Parameter Store).
- [ ] **Segregação de ambientes** (dev/staging/prod distintos).
- [ ] **Rotação periódica** de senhas e chaves.
- [ ] **Principle of Least Privilege** para usuários de DB e S3.
- [ ] **Acesso restrito** a variáveis de ambiente em produção.
- [ ] **Auditoria de acesso a Segredos**.

---

## 📦 Observabilidade & Alertas
- [ ] **/health** retorna status de DB/Redis e fila Horizon.
- [ ] **Alarmes 5xx > 2%** e **p95 > 1s** configurados.
- [ ] **Correlações via X-Request-ID** em logs e traces.
- [ ] **Alerta de brute force** (falhas > N/min).
- [ ] **Alerta de 4xx anormais**: spikes contínuos >10% em 5min.

---

## 🧪 Testes de Segurança
- [ ] Testes automáticos de auth, tenancy, RBAC, filtros, auditoria.
- [ ] Casos positivos/negativos (superuser cross-tenant permitido vs bloqueado).
- [ ] Testes de rate-limit (429) e upload inválido.
- [ ] **Fuzzing básico** em inputs (OWASP ZAP/Faker).
- [ ] **Testes de upload** incluem arquivos renomeados (.php → .jpg).
- [ ] **Dependabot/Snyk** habilitado para libs PHP/JS.
- [ ] **Testes automáticos de headers** (CSP, HSTS, CORP, COOP).

---

## 🧭 Próximos Passos
- Revisão periódica do checklist antes de cada release.
- Automação de linting de segurança (Larastan, PHPStan, SonarQube).
- Planejar integração com WAF e varredura SAST/DAST.
