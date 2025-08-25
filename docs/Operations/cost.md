# AWS — Custo & Evolução

## MVP (fase inicial)
Infra mínima para entregar valor rápido e com baixo custo operacional.

- **Compute**: Elastic Beanstalk (EC2 single instance ou auto-scaling básico) → alternativa moderna: **App Runner** (custo menor e mais simples de operar).  
- **DB**: RDS (Postgres/MySQL) **Single-AZ**.  
- **Storage**: S3 (imagens de veículos).  
- **CDN**: CloudFront (opcional para SPA + imagens).  
- **Logs/Métricas**: CloudWatch (logs estruturados, alarmes simples, retenção 30 dias).  
- **Cache/Fila interna**: Redis **obrigatório em produção** (ElastiCache) para cache + Horizon (jobs Laravel).  
- **E-mail**: SES (reset de senha).  
- **Rede**: NAT Gateway (caso VPC privada) — custo fixo por hora.  
- **TLS/HTTPS**: Certificate Manager (ACM, gratuito).  

### Custos típicos (estimativa baixa escala)
- EB + EC2 t3.small ou App Runner: ~25–40 USD/mês.  
- RDS Single-AZ db.t3.micro: ~15–20 USD/mês.  
- S3 + CloudFront: 5–15 USD/mês (baixo volume).  
- ElastiCache Redis micro: ~15–20 USD/mês.  
- NAT Gateway: ~32 USD/mês + tráfego.  
- SES: 0,10 USD/1k emails.  
**Total**: ~90–130 USD/mês (região us-east-1, tráfego baixo).  

---

## Evolução (escala/robustez)
Quando clientes e dados crescerem, ajustar para resiliência, segurança e observabilidade.

- **Compute**: migrar EB → ECS/EKS (containers gerenciados, Fargate).  
- **DB**: RDS Multi-AZ (alta disponibilidade) + Read Replicas → alternativa: **Aurora Serverless v2** para elasticidade automática.  
- **Storage**: S3 + replicação cross-region (DR) + Glacier para arquivamento.  
- **CDN**: CloudFront com WAF + Shield para mitigação de ataques.  
- **Cache/Queue**: Redis cluster (ElastiCache) para cache/Horizon + SQS para filas distribuídas.  
- **Observabilidade**: CloudWatch + OpenTelemetry/ELK + tracing distribuído. Logs frios exportados para **S3 + Athena**.  
- **Segurança**:  
  - Secrets Manager (segredos fora do código).  
  - IAM Roles segmentados (principle of least privilege).  
  - WAF + Shield Advanced.  
  - Backup automatizado (RDS snapshots, S3 Lifecycle → Glacier).  

### Custos típicos (escala média)
- ECS/EKS cluster (infra básica): ~100–200 USD/mês.  
- RDS Multi-AZ db.t3.medium: ~80–100 USD/mês.  
- Redis cluster 2–3 nós: 60–120 USD/mês.  
- SQS: poucos USD/mês.  
- WAF ACL: ~20 USD/mês.  
- Observabilidade avançada (ELK/OpenSearch): 50–150 USD/mês.  
- NAT Gateway + tráfego cross-AZ: 50–100 USD/mês (dependendo do volume).  
**Total**: ~400–700 USD/mês.  

---

## Trade-offs

| Aspecto            | MVP (EB/AppRunner+Single-AZ)                 | Evolução (ECS/EKS+Multi-AZ+WAF)              |
|--------------------|-----------------------------------------------|----------------------------------------------|
| **Custo**          | Baixo (~90–130 USD/mês)                      | Médio/Alto (~400–700 USD/mês)                 |
| **Disponibilidade**| Single-AZ (downtime em falha AZ)             | Multi-AZ/Serverless (alta disponibilidade)   |
| **Escalabilidade** | Auto-scaling básico (EB/App Runner)          | Horizontal robusto (ECS/EKS, Aurora Serverless) |
| **Segurança**      | HTTPS + boas práticas básicas                | WAF, Shield, Secrets Manager, IAM granular   |
| **Operabilidade**  | Simples (rápido para dev)                    | Complexo (IaC, observabilidade completa)     |

---

## Regras práticas
- **Sempre usar Redis em produção** (cache + Horizon).  
- **Migrar DB para Multi-AZ ou Aurora Serverless** antes de 50 tenants ativos.  
- **Adicionar WAF** antes de exposição pública ampla.  
- **Planejar ECS/EKS** quando EB/App Runner começar a ter gargalos de deploy/escala.  
- **Exportar logs antigos para S3 + Athena** para reduzir custo.  
- **Incluir NAT Gateway e tráfego cross-AZ no planejamento financeiro** (custos ocultos mais comuns).  
