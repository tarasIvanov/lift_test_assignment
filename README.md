# Lift Test Assignment — REST API

REST API для збереження та отримання контактів з асинхронною обробкою через RabbitMQ
та визначенням країни за IP адресою.

## Quick Start

```bash
make up
make migrate
make worker
```

Або без Makefile:
```bash
docker compose up --build -d
docker compose exec lift-app php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec lift-app php bin/console messenger:consume async -vv
```

Swagger UI: http://localhost:8080/api/doc

## Architecture

- **CQRS** — POST кладе повідомлення в RabbitMQ, GET читає з БД. Запис і читання строго розділені.
- **Service Layer** — контролер тонкий, бізнес-логіка в `ContactService`.
- **DTO** — `CreateContactRequestDTO` з валідаційними атрибутами + `#[MapRequestPayload]`, `ContactResponse` з factory `fromEntity()`.
- **Redis кешування** — GET відповіді кешуються на 10с (TTL-based, без інвалідації).
- **Індекс на `lastName`** — оптимізація сортування.
- **Логування** — помилки геолокації логуються через PSR-3 (warning/notice).
- **Global Exception Handler** — всі помилки повертаються в JSON форматі.
- **Docker Compose** — PHP-FPM, Nginx, MySQL, RabbitMQ, Redis з healthchecks.
- **PHPUnit**, **PHPStan level 8**, **PHP-CS-Fixer (PSR-12)**.
