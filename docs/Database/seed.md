# Plano de Seeds & Dados de Teste

## Objetivo
Fornecer dados consistentes para rodar a coleção Postman/Insomnia **fim a fim**, validando cenários de sucesso, paginação, unicidade por tenant e casos de erro.

---

## Tenants
- **acme** (empresa fictícia 1)  
- **globex** (empresa fictícia 2)  

---

## Usuários
### acme
- **owner@acme.com** / senha: `password` (Owner)  
- **agent@acme.com** / senha: `password` (Agent)  

### globex
- **owner@globex.com** / senha: `password` (Owner)  
- **agent@globex.com** / senha: `password` (Agent)  

### superuser
- **admin@system.com** / senha: `password` (superuser, `tenant_id = NULL`)

---

## Veículos
### Tenant: acme
- 15 veículos para testar **paginação** (`/vehicles?page=2&per_page=10`).  
- Incluem variação de **marca, modelo, ano, preço, status**.  
- Um veículo com placa `ABC-1234`.

### Tenant: globex
- 10 veículos diversos.  
- Um veículo também com placa `ABC-1234` (valida **constraint única apenas dentro do mesmo tenant**).  

---

## Exemplos de veículos (simplificado)

```json
{
  "brand": "Toyota",
  "model": "Corolla",
  "year": 2020,
  "price": 85000,
  "status": "available",
  "plate": "ABC-1234"
}
````

```json
{
  "brand": "Ford",
  "model": "Focus",
  "year": 2019,
  "price": 72000,
  "status": "sold",
  "plate": "XYZ-5678"
}
```

---

## Casos de erro (para documentação/testes)

* **year: 1800** ou **price: -1000** → gera `422 VALIDATION_ERROR`.

  > ⚠️ Mantidos **comentados** no seed para não quebrar a execução, mas listados na doc como exemplos.
* **lead com vehicle\_id inexistente** → gera `404 NOT_FOUND`.

  > Pode ser simulado manualmente em `/leads`.

---

## Auditoria (logs seed)

Cria um registro inicial em `audit_logs`:

```json
{
  "event": "cross_tenant_access",
  "request_id": "seed-uuid",
  "superuser_id": 1,
  "target_tenant": "acme",
  "actor_tenant": null,
  "route": "GET /api/v1/vehicles",
  "status": 200,
  "timestamp": "2025-08-20T12:00:00Z"
}
```

Esse seed demonstra:

* **Superuser acessando cross-tenant**
* **Formato padronizado de log JSON**
* Integração com Observabilidade

---

## Execução

```bash
php artisan migrate:fresh --seed
```

Após rodar, é possível:

1. **Logar** com qualquer usuário acima.
2. **Listar veículos** (paginações reais).
3. **Testar duplicidade de placa cross-tenant**.
4. **Validar erros 422/404** com os exemplos citados.
5. **Conferir logs seedados** na tabela `audit_logs`.

```
