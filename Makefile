install:
	composer install
	cp .env.example .env || true
	php artisan key:generate
	php artisan migrate --seed

up:
	docker compose up --build -d

down:
	docker compose down

test:
	php artisan test

lint:
	vendor/bin/pint --test

migrate:
	php artisan migrate

seed:
	php artisan db:seed

docs:
	@echo "Swagger UI: http://localhost:8000/api/documentation"
