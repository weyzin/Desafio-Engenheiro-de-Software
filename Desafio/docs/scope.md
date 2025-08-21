# Scope — Desafio Laravel + React + AWS (MVP)

> Fonte: PDF do Desafio (seções: Multi-tenancy, RBAC, CRUD/Filtros/Paginação, Auditoria, API REST/OpenAPI, Front React, NFRs, AWS — desenho sem deploy)

## Visão Geral
MVP composto por:  
1) **API Laravel** multitenant, segura e observável;  
2) **Frontend React** (SPA) com autenticação e telas de veículos/usuários/leads;  
3) **Desenho de Infra AWS** (somente explicação/diagrama, sem provisionamento).

## Entidades mínimas
* **Tenant**: organização lógica; chave primária `id`; resolução por subdomínio e fallback por header `X-Tenant`. *(PDF: Multi-tenancy)*
* **User**: atributos básicos + `tenant_id` (exceto superuser global) + papéis {superuser, owner, agent}. *(PDF: RBAC)*
* **Vehicle**: atributos (brand, model, year, price, images, status); pertencente a um tenant. *(PDF: CRUD Veículos)*
* **Lead (opcional)**: contatos interessados em veículos; CRUD simples no frontend + tela de detalhe com notas (placeholder de entidade para MVP). *(PDF: Leads, funcionalidade opcional)*

## Requisitos Funcionais
* **Autenticação (SPA)**: login com Sanctum; suportar cookies HttpOnly *ou* tokens bearer; endpoint de recuperação de senha; endpoint `/api/v1/me`. *(PDF: Autenticação)*
* **Autorização (RBAC)**: Policies/Gates para CRUD e escopo de tenant; superuser global; owner e agent limitados ao próprio tenant. *(PDF: RBAC)*
* **Multi-tenancy (single-DB)**: `tenant_id` em tabelas; Global Scopes/Policies bloqueiam acesso cross-tenant; resolução por subdomínio e header. *(PDF: Multi-tenancy)*
* **CRUD Vehicles**: `GET/POST/PUT/DELETE /api/v1/vehicles` com validação, auditoria e regras de tenant. *(PDF: CRUD Veículos)*
* **CRUD Users (frontend)**: Owners podem criar/editar/excluir usuários dentro de seu tenant. *(PDF: RBAC/Usuários)*
* **Filtros & Paginação**: filtros por `brand`, `model`, `price_min`, `price_max`; paginação com `meta/links` consistentes; **ordenação como bônus**. *(PDF: Filtros/Paginação)*
* **Auditoria**: campos `created_by`, `updated_by`; `deleted_by` e soft delete como **funcionalidades extras**; logs estruturados por ação. *(PDF: Auditoria)*
* **Erros padronizados**: JSON `{code, message, details}` (ex.: `VALIDATION_ERROR`), status HTTP adequados. *(PDF: API/Erros)*

## Requisitos de Frontend (React SPA)
* **Fluxos de sessão**: login, logout, “esqueci a senha”, manutenção do estado autenticado. *(PDF: Front/Autenticação)*
* **Listagens**: Lista de veículos com filtros, paginação, ordenação (bônus), estados de loading/erro/empty. *(PDF: Front/Listas)*
* **Telas de detalhe/edição**: Criar/editar/excluir veículo (respeitando RBAC e tenancy) + upload de imagens (S3). *(PDF: Front/CRUD)*
* **CRUD de usuários**: acessível apenas a Owners, respeitando escopo de tenant. *(PDF: Front/Usuários)*
* **CRUD de leads (opcional)**: com listagem, detalhe e notas simples. *(PDF: Front/Leads)*
* **Seleção de tenant**: por subdomínio; fallback manual via campo/header `X-Tenant`. *(PDF: Multi-tenancy/Front)*

## Requisitos de API
* **REST & Versão**: prefixo `/api/v1`; contratos documentados em OpenAPI (YAML); padrões de resposta. *(PDF: API REST/OpenAPI)*
* **Validações**: server-side via Form Requests; mensagens coerentes com formato de erro. *(PDF: Validações)*
* **Rate-limit**: políticas diferenciadas para `/auth/*` (mais restrito) e rotas autenticadas; chaves por tenant/usuário/IP. *(PDF: Rate-limit)*

## NFRs (Não-Funcionais)
* **Segurança**: CORS restritivo; cookies HttpOnly+SameSite ou bearer tokens; headers (CSP básica, HSTS opcional, X-Content-Type-Options, etc.); segregação de segredos. *(PDF: Segurança)*
* **Escalabilidade/Custo**: arquitetura preparada para escalar horizontalmente; foco em baixo custo no MVP. *(PDF: NFRs/Cloud)*
* **Observabilidade**: logs JSON (campos: `request_id`, `tenant_id`, `user_id`, `route`, `status`, `latency_ms`), endpoint `/health`, métricas mínimas (req/s, p95, erros). *(PDF: Observabilidade)*
* **Testes**: cobertura mínima de **API (unit e feature)** + **E2E no frontend** para fluxos de login e CRUD de veículos/leads.  
   Testes críticos: **auth, tenancy, RBAC, filtros/paginação, auditoria**. *(PDF: Testes)*
* **Confiabilidade**: timeouts, idempotência nos uploads e boas práticas de erro/retry onde aplicável. *(PDF: NFRs)*

## AWS (Somente desenho/justificativa; sem deploy)
* **Web**: React estático em S3 + CloudFront (cache/HTTPS).
* **API**: atrás de ALB → **Elastic Beanstalk no MVP** (deploy automático via GitHub Actions); opção futura de migrar para **ECS Fargate ou EKS** conforme demanda de escala.  
* **Banco**: RDS (Single-AZ no MVP).  
* **Storage**: S3 (imagens).  
* **Observabilidade**: CloudWatch (logs/métricas/alarmes).  
* **Segredos**: Secrets Manager ou SSM Parameter Store.  
* **Segurança**: WAF opcional.  
* **CI/CD**: **MVP: GitHub Actions → EB auto-deploy**; evolução: CodeDeploy/ECS/EKS para cenários mais complexos.  
*(PDF: AWS — trade-off entre simplicidade inicial vs. evolução para containers/k8s)*
