flowchart TB
    U[Usuário / SPA / Postman] -->|HTTPS| CF[CloudFront]
    CF --> WAF[WAF + Shield]
    WAF --> ECS[ECS/EKS - Laravel em containers (Fargate)]

    subgraph VPC [VPC]
      direction TB
      ECS --> RDS[(RDS Multi-AZ + Read Replicas)]
      ECS --> Redis[(ElastiCache Redis Cluster)]
      ECS --> SQS[(SQS - filas assíncronas)]
      ECS --> S3[(S3 - imagens e artefatos)]
      ECS --> SM[(AWS Secrets Manager)]
      ECS --> NAT[NAT Gateway]
    end

    ECS --> CW[CloudWatch - logs e métricas]
    ECS --> OTEL[OpenTelemetry - tracing]
    CW --> S3Logs[(S3 - arquivamento de logs)]
    S3Logs --> Athena[Athena - consultas de logs]
    S3 --> CRR[(Replicação cross-region)]
    RDS --> Snap[(Snapshots e backups)]

    SM --> ECS
