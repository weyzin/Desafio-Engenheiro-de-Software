# Runbook de Observabilidade e Resposta a Incidentes

## Objetivo
Definir thresholds, alertas e procedimentos de resposta para manter a operação estável e segura.

---

## Níveis de Severidade

- **Warning**  
  - Latência p95 > 500ms por 5 min.  
  - Erros 4xx > 10% contínuos.  
  - Fila de jobs com atraso > 2 min.  

- **Critical**  
  - Latência p95 > 1s.  
  - Erros 5xx > 10% em 5 min.  
  - Fila parada (>5 min sem jobs).  
  - Banco indisponível.  

> Warning → Slack #alerts  
> Critical → PagerDuty + Slack #incident  

---

## Auditoria Cross-Tenant
- Todo acesso cross-tenant de **superuser** deve gerar alerta de segurança (`#secops`).  
- Log capturado: `request_id`, `superuser_id`, `target_tenant`, `route`, `timestamp`.  
- SLO: **zero acessos não justificados/mês**. Cada evento → ticket de justificativa.

---

## Procedimentos de Mitigação
- **Latência alta:** revisar queries (`EXPLAIN`), cache Redis, profiling (Telescope/APM).  
- **Fila atrasada:** verificar workers ativos (`horizon:status` / `ecs describe-tasks`), scale out workers.  
- **Erros 5xx:** revisar logs da última release, rollback via **pipeline CI/CD** (GitHub Actions).  
- **Banco fora do ar:** testar `pg_isready`/`mysqladmin ping`, failover para réplica (se existir).  

---

## Chaos Engineering
- **Simular DDoS / rate-limit:** stress em `/auth/login` → esperado `429` com `Retry-After`.  
- **Simular fila travada:** desligar workers → alerta em <5 min.  
- **Simular DB indisponível:** desligar réplica em staging → alerta crítico em <1 min.  

---

## Retenção
- Logs (CloudWatch): 30 dias, exportados para S3/Glacier.  
- Métricas: 14 dias.  
- Traces: 7 dias.  
- Audit logs: 90 dias (mínimo).  

---

## Canais
- `#alerts` Slack: warnings.  
- `#incident` Slack: críticos.  
- `#secops` Slack: segurança cross-tenant.  
- PagerDuty: escalonamento.  

---

## SLA
- Warning: triagem ≤ 15 min.  
- Critical: resposta < 5 min.  
- Cross-tenant: análise ≤ 30 min.  
