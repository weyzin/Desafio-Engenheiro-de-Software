# Desafio — API v1 (Laravel)

Guia rápido para rodar, avaliar e testar o MVP multi-tenant.

---

![CI](https://github.com/weyzin/Desafio-Engenheiro-de-Software/actions/workflows/ci.yml/badge.svg)

## 🚀 Visão Geral

API **multi-tenant** em Laravel para gestão de veículos.
Inclui:

* Autenticação (Sanctum com cookies HttpOnly).
* CRUD de veículos.
* Listagem de usuários (Owner).
* Auditoria e rate-limit conforme ADRs.

Todos os responses seguem padrões definidos na **OpenAPI** e nos **ADRs**.

---

## 🛠️ Requisitos

* **PHP 8.2+** com Composer.
* **Node.js** (para frontend, se aplicável).
* **MySQL ou PostgreSQL**.
* **Redis** (obrigatório em produção; opcional em dev/test).
* **Postman/Insomnia** (para testar a coleção).

---

## ⚙️ Setup e Execução

1. Clone o repositório e instale dependências:

   ```bash
   git clone <repo-url>
   cd <repo>
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

2. Configure `.env`:

   * Banco (`DB_*`).
   * Redis (`REDIS_HOST`, etc).
   * Mailer (`MAIL_*`).

3. Execute migrações e seeds:

   ```bash
   php artisan migrate:fresh --seed
   ```

4. Suba o servidor:

   ```bash
   php artisan serve
   ```

   API disponível em:
   👉 `http://localhost:8000/api/v1`

---

## 📦 Seeds (dados iniciais)

**Tenants**:

* `acme`
* `globex`

**Usuários**:

* Superuser → `admin@root.com / Password!123`
* Owner → `owner@acme.com / Password!123`
* Agent → `agent@acme.com / Password!123`

**Veículos**:

* \~10 registros com marcas, anos e preços variados.

---

## 🧪 Postman / Insomnia

1. Importe `api-collection.postman.json`.

2. Configure variáveis:

   * `base_url` → `http://localhost:8000/api/v1`
   * `x_tenant` → `acme`
   * `email` / `password` → credenciais acima

3. Fluxo sugerido:

   * `Auth > POST /auth/login`
   * `Auth > GET /me`
   * `Vehicles > GET /vehicles`
   * `Vehicles > POST /vehicles`
   * `Vehicles > GET /vehicles/{id}`
   * `Vehicles > PUT /vehicles/{id}`
   * `Vehicles > DELETE /vehicles/{id}`
   * `Auth > POST /auth/logout`

Inclui exemplos de erros: `401`, `403`, `404`, `422`, `429`.

---

## 🔒 Segurança

* **CSRF (Sanctum)** → `GET /sanctum/csrf-cookie` + header `X-XSRF-TOKEN`.
* **Cookies** → HttpOnly + SameSite=Strict.
* **Rate-limit** → segmentado (429 + Retry-After).
* **Headers de segurança** → CSP, X-Frame-Options, HSTS, etc.
* **Tenancy** → enforced via subdomínio + Global Scope.

---

## 📚 Documentação Relacionada

- [ADR](Desafio/docs/ADR/) — Decisões arquiteturais
- [Architecture](Desafio/docs/Architecture/) — Diagramas e visão técnica
- [Database](Desafio/docs/Database/) — Migrations e seeds
- [Operations](Desafio/docs/Operations/) — Runbooks, custos e tenancy
- [Security](Desafio/docs/Security/) — Checklist AppSec, catálogos de erro, versionamento
- [Testing](Desafio/docs/Testing/) — Coleções Postman e OpenAPI
- [Scope.md](Desafio/docs/scope.md) — Escopo funcional do MVP

---

## 🚧 Limitações (MVP)

* Reset de senha → apenas envio de e-mail (sem fluxo completo de token).
* Autorização granular → parcial.
* BearerAuth → documentado mas não implementado (Sanctum apenas).
