# MVP — Arquitetura AWS

```mermaid
flowchart TB
    User[Usuário / Postman] -->|HTTP/HTTPS| CF[CloudFront (opcional)]
    CF --> EB[Elastic Beanstalk (Laravel API)]

    EB --> RDS[(RDS Single-AZ Postgres/MySQL)]
    EB --> Redis[(ElastiCache Redis)]
    EB --> S3[(S3 - Imagens de veículos)]
    EB --> SES[(SES - Emails)]
    EB --> CW[CloudWatch - Logs/Métricas]

    RDS --> CW
    Redis --> CW
    S3 --> CW
    SES --> CW
