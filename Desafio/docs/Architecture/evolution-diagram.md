# Evolução — Arquitetura AWS (Alta Disponibilidade)

```mermaid
flowchart TB
    %% =================== EDGE ENTRY ===================
    U[Usuário / SPA / Postman]
    U -->|HTTP/HTTPS| CF[CloudFront]
    CF -->|proteção| WAF[WAF + Shield]

    %% =================== COMPUTE ===================
    WAF --> ECS[ECS/EKS (Laravel API em containers / Fargate)]
    subgraph VPC [VPC]
      direction TB
      ECS --> RDS[(RDS Multi-AZ<br/>+ Read Replicas)]
      ECS --> Redis[(ElastiCache Redis Cluster)]
      ECS --> SQS[(SQS - filas assíncronas)]
      ECS --> S3[(S3 - imagens, artefatos)]
      ECS --> SM[(Secrets Manager)]
      ECS --> NAT[NAT Gateway]
    end

    %% =================== OBSERVABILIDADE ===================
    ECS --> CW[CloudWatch Logs/Métricas]
    ECS --> OTEL[OpenTelemetry / Tracing]
    CW -->|export| S3Logs[(S3 - logs frios)]
    S3Logs --> Athena[Athena - consulta de logs]
    
    %% =================== RESILIÊNCIA ===================
    S3 --> CRR[(Replicação Cross-Region)]
    RDS --> Snap[Snapshots/Backups]
    
    %% =================== POLÍTICAS E IAM ===================
    SM -->|roles| ECS
    S3 -.->|IAM Policies por prefixo de tenant| ECS
