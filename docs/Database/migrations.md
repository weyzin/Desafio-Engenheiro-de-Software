# 🗄️ DDL / Migrations — Esqueleto 

> Banco relacional (MySQL 8+ ou PostgreSQL 13+).  
> Estratégia: **single-DB** com `tenant_id` (ADR-01), **auditoria** (`created_by`, `updated_by`, `deleted_by`, `impersonated_by`) e **constraints por tenant**.

---

## Ordem recomendada de migrations

1. `tenants`  
2. `tenant_domains`  
3. `users`  
4. `vehicles`  
5. (futuro) `vehicle_images`  
6. índices auxiliares / constraints adicionais

---

## 1) Tenants

```sql
CREATE TABLE tenants (
  id            UUID PRIMARY KEY,               -- PostgreSQL
  -- CHAR(36) em MySQL para consistência
  name          VARCHAR(150) NOT NULL,
  created_at    TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
  -- Atualização de updated_at deve ser feita pela aplicação ou gatilho
);
````

### 1.1) Tenant domains (custom domains)

```sql
CREATE TABLE tenant_domains (
  id         BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  tenant_id  UUID NOT NULL,  -- PG: UUID; MySQL: CHAR(36)
  domain     VARCHAR(255) NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_tenant_domains_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
      ON DELETE CASCADE
);

-- Garantir domínio exclusivo (case insensitive)
-- PostgreSQL
CREATE UNIQUE INDEX ux_tenant_domains_domain ON tenant_domains (LOWER(domain));
-- MySQL
-- usar collation case-insensitive
```

---

## 2) Users (com superuser global)

```sql
CREATE TABLE users (
  id                        BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  tenant_id                 UUID NULL,
  name                      VARCHAR(150) NOT NULL,
  email                     VARCHAR(254) NOT NULL,   -- aumentado para 254
  password_hash             VARCHAR(255) NOT NULL,
  role                      VARCHAR(20) NOT NULL
                             CHECK (role IN ('superuser','owner','agent')),

  -- Auditoria e segurança
  created_by                BIGINT NULL,
  updated_by                BIGINT NULL,
  deleted_by                BIGINT NULL,
  impersonated_by           BIGINT NULL,
  last_login_at             TIMESTAMPTZ NULL,
  failed_logins             INT NOT NULL DEFAULT 0,
  last_password_change_at   TIMESTAMPTZ NULL,

  created_at                TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at                TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
  deleted_at                TIMESTAMPTZ NULL,

  CONSTRAINT fk_users_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
      ON DELETE SET NULL,

  CONSTRAINT fk_users_created_by
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_users_updated_by
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_users_deleted_by
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_users_impersonated_by
    FOREIGN KEY (impersonated_by) REFERENCES users(id) ON DELETE SET NULL
);
```

Índices por tenant já descritos (unicidade de e-mail global vs tenant).

---

## 3) Vehicles

```sql
CREATE TABLE vehicles (
  id           BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  tenant_id    UUID NOT NULL,
  brand        VARCHAR(80) NOT NULL,
  model        VARCHAR(80) NOT NULL,
  year         INT NOT NULL CHECK (year >= 1900 AND year <= 2100),
  price        DECIMAL(12,2) NOT NULL CHECK (price >= 0),
  status       VARCHAR(16) NOT NULL CHECK (status IN ('available','reserved','sold')),
  images_json  JSONB NULL,                  -- PostgreSQL: JSONB; MySQL: JSON
  -- opcional: plate, vin

  -- Auditoria
  created_by   BIGINT NULL,
  updated_by   BIGINT NULL,
  deleted_by   BIGINT NULL,

  created_at   TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
  deleted_at   TIMESTAMPTZ NULL,

  CONSTRAINT fk_vehicles_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
      ON DELETE CASCADE,

  CONSTRAINT fk_vehicles_created_by
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_vehicles_updated_by
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_vehicles_deleted_by
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);
```

### Índices

```sql
-- filtros básicos
CREATE INDEX ix_vehicles_brand   ON vehicles (tenant_id, brand);
CREATE INDEX ix_vehicles_model   ON vehicles (tenant_id, model);
CREATE INDEX ix_vehicles_year    ON vehicles (tenant_id, year);
CREATE INDEX ix_vehicles_price   ON vehicles (tenant_id, price);
CREATE INDEX ix_vehicles_status  ON vehicles (tenant_id, status);

-- ordenação por recência
CREATE INDEX ix_vehicles_recent  ON vehicles (tenant_id, created_at DESC);

-- disponíveis até preço X
-- PostgreSQL
CREATE INDEX ix_vehicles_available_price
  ON vehicles (price) WHERE status = 'available';
-- MySQL: usar coluna gerada como já descrito
```

---

## 4) Vehicle Images (futuro)

(mesmo texto, sem mudanças relevantes)

---

## 5) Notas de integridade e segurança

* Use TIMESTAMPTZ em PostgreSQL para todos campos temporais.
* FKs sempre com o mesmo tipo do campo referenciado (UUID vs CHAR(36)).
* Circularidade no seed: popular usuários primeiro, depois atualizar created\_by/updated\_by em segunda passada ou aceitar NULL inicial.
* Soft delete, auditoria e unicidade por tenant mantidos.
* Coerência API ↔ DB: API usa `images`, DB persiste `images_json`.

