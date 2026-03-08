# Lift Test Assignment — REST API

REST API для збереження та отримання контактів з асинхронною обробкою через RabbitMQ
та визначенням країни за IP адресою.

## Quick Start

```bash
docker compose up --build -d
docker compose exec lift-app php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec lift-app php bin/console messenger:consume async -vv
```
