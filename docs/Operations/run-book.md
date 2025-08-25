# Runbook de Observabilidade e Resposta a Incidentes

## Objetivo
Definir thresholds claros, ações de mitigação, canais de alerta e práticas de retenção de dados para manter a operação estável e segura.

---

## Níveis de Severidade de Alertas

- **Warning**  
  - Latência `p95 > 500ms` por 5 minutos.  
  - Erros 400/422 > 10% de forma contínua.  
  - Fila de jobs com atraso > 2 minutos.  

- **Critical**  
  - Latência `p95 > 1s`.  
  - Erros 5xx > 10% em 5 minutos.  
  - Fila parada (nenhum job processado em 5 minutos).  
  - Banco de dados indisponível ou perda de conectividade.  

> Alerts **Warning** → Slack #alerts.  
> Alerts **Critical** → PagerDuty + Slack #incident.

---

## Auditoria Cross-Tenant (Segurança)

- Sempre que `audit_logs` registrar evento `cross_tenant_access`, gerar **alerta de segurança** no canal `#secops`.  
- Contexto capturado:
  - `request_id`, `superuser_id`, `target_tenant`, `route`, `timestamp`.  
- **KPI de Segurança (SLO):**  
  - **Zero acessos cross-tenant não justificados por mês.**  
  - Cada evento deve ter ticket de justificativa ou incidente associado.  

---

## Procedimentos de Mitigação

- **Latência alta (Warning/Critical)**  
  - Usar Laravel Telescope/Debugbar ou APM (Datadog/NewRelic).  
  - Identificar slow queries → rodar `EXPLAIN`.  
  - Avaliar caching/Redis.  

- **Fila atrasada**  
  - Verificar workers ativos.  
  - Executar `artisan horizon:status` ou `ecs describe-tasks`.  
  - Scale out de workers (`php artisan horizon:terminate` para reciclar stuck workers).  

- **Erros 5xx em burst**  
  - Consultar logs da última release.  
  - Avaliar rollback:  
    - `php artisan migrate:rollback` (se suspeita de migração).  
    - Redeploy da versão anterior via CI/CD tag.  
  - Se não estabilizar → acionar time de Infra.  

- **Banco fora do ar**  
  - Rodar `pg_isready` ou `mysqladmin ping`.  
  - Se indisponível → failover para réplica (se configurada).  
  - Caso contrário → abrir incidente crítico e envolver DBA.  

---

## Runbooks de Teste (Chaos Engineering)

- **Simular DDoS / Rate-limit**  
  - Usar `ddos-simulator` contra `/auth/login`.  
  - Esperado: retorno `429 RATE_LIMIT_EXCEEDED` com `Retry-After`.  

- **Simular fila travada**  
  - Executar `php artisan queue:work --stop-when-empty`.  
  - Aguardar jobs pendentes → alerta deve disparar em <5 minutos.  

- **Simular DB indisponível**  
  - Desligar a réplica do banco em staging.  
  - Esperado: alerta crítico de indisponibilidade em <1 minuto.  

---

## Retenção de Dados

- **Logs (CloudWatch / ELK)** → 30 dias.  
- **Métricas (Prometheus)** → 14 dias.  
- **Traces (OTEL Collector / Jaeger)** → 7 dias.  
- **Audit logs (cross-tenant, segurança)** → 90 dias (mínimo).  

---

## Canais e Responsabilidades

- **#alerts** (Slack): notificações automáticas de Warning.  
- **#incident** (Slack): incidentes críticos (Critical).  
- **#secops** (Slack): alertas de segurança (cross-tenant).  
- **PagerDuty**: escalonamento para engenheiros de plantão.  

---

## SLA e Tempo de Resposta

- **Warning** → triagem em até 15 minutos.  
- **Critical** → resposta imediata (<5 minutos).  
- **Cross-tenant access** → análise em até 30 minutos.  
