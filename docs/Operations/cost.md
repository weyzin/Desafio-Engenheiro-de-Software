# AWS — Custo & Evolução

## MVP (fase inicial)
Infra mínima para entregar valor rápido e com baixo custo operacional.

- **Compute**: ECS Fargate (containers Laravel). Alternativas: Elastic Beanstalk ou App Runner (menos controle, mais barato).  
- **DB**: RDS (Postgres/MySQL) **Single-AZ**.  
- **Storage**: S3 (imagens de veículos).  
- **CDN**: CloudFront (opcional para SPA + imagens).  
- **Logs/Métricas**: CloudWatch (logs estruturados, retenção 30 dias).  
- **Cache/Fila**: Redis **obrigatório em produção** (ElastiCache) para cache + Horizon. Em dev/CI pode usar memória.  
- **E-mail**: SES (reset de senha).  
- **Rede**: NAT Gateway (caso VPC privada). Recomendado usar **VPC Endpoints** (S3, ECR, Logs) para reduzir custo.  
- **TLS/HTTPS**: Certificate Manager (ACM, gratuito).  

### Custos típicos (estimativa baixa escala)
| Serviço          | Tipo/Plano              | Custo aproximado (us-east-1) |
|------------------|-------------------------|-------------------------------|
| ECS Fargate      | 2 tasks pequenas        | 25–40 USD/mês                |
| RDS Single-AZ    | db.t3.micro             | 15–20 USD/mês                |
| S3 + CloudFront  | baixo volume            | 5–15 USD/mês                 |
| Redis            | cache.t4g.micro         | 15–20 USD/mês                |
| NAT Gateway      | 1 unidade + tráfego     | 32 USD/mês + egress          |
| SES              | 1k emails               | 0,10 USD                     |
| CloudWatch       | logs + métricas         | 5–10 USD/mês                 |
**Total**: ~90–130 USD/mês.  

---

## Evolução (escala/robustez)
- **Compute**: ECS/EKS (containers Fargate ou EC2 Spot).  
- **DB**: RDS Multi-AZ + Read Replicas ou Aurora Serverless v2.  
- **Storage**: S3 + cross-region replication + Glacier.  
- **CDN**: CloudFront + WAF + Shield.  
- **Cache/Queue**: Redis cluster (ElastiCache) + SQS.  
- **Observabilidade**: CloudWatch + OTEL + exportação para S3/Athena.  
- **Segurança**: Secrets Manager (ou Parameter Store), IAM granular, WAF, backup automatizado.  

### Custos típicos (escala média)
| Serviço          | Tipo/Plano              | Custo aproximado (us-east-1) |
|------------------|-------------------------|-------------------------------|
| ECS Fargate/EKS  | cluster básico          | 100–200 USD/mês              |
| RDS Multi-AZ     | db.t3.medium            | 80–100 USD/mês               |
| Redis cluster    | 2–3 nós                 | 60–120 USD/mês               |
| SQS              | standard                | 5–10 USD/mês                 |
| WAF ACL          | 1 ACL                   | 20 USD/mês                   |
| Observabilidade  | Logs + OTEL             | 50–150 USD/mês               |
| NAT Gateway      | 2–3 unidades + tráfego  | 50–100 USD/mês               |
**Total**: ~400–700 USD/mês.  

---

## Regras práticas
- Redis sempre em produção.  
- Migrar DB para Multi-AZ ou Aurora Serverless antes de 50 tenants.  
- Adicionar WAF antes de abertura pública ampla.  
- Migrar para ECS/EKS quando EB/App Runner saturarem.  
- Exportar logs antigos para S3 + Glacier.  
- Planejar NAT e tráfego cross-AZ (custos ocultos).  
