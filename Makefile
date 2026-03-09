up:
	docker compose up --build -d

down:
	docker compose down

migrate:
	docker compose exec lift-app php bin/console doctrine:migrations:migrate --no-interaction

worker:
	docker compose exec lift-app php bin/console messenger:consume async -vv

test:
	docker compose exec lift-app php vendor/bin/phpunit

phpstan:
	docker compose exec lift-app php vendor/bin/phpstan analyse

cs-fix:
	docker compose exec lift-app php vendor/bin/php-cs-fixer fix
