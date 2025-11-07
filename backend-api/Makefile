.PHONY: help test test-auth test-all test-filter test-file pint up down restart shell migrate fresh seed logs

# Default target
help:
	@echo "Available commands:"
	@echo "  make up          - Start Docker containers"
	@echo "  make down        - Stop Docker containers"
	@echo "  make restart     - Restart Docker containers"
	@echo "  make shell        - Open shell in app container"
	@echo "  make test        - Run all tests"
	@echo "  make test-auth    - Run authentication tests"
	@echo "  make test-all     - Run all tests with coverage"
	@echo "  make test-filter  - Run filtered tests (usage: make test-filter FILTER=testName)"
	@echo "  make test-file    - Run specific test file (usage: make test-file FILE=tests/Feature/Auth/LoginTest.php)"
	@echo "  make pint         - Run Laravel Pint code formatter"
	@echo "  make migrate      - Run database migrations"
	@echo "  make fresh        - Fresh migration with seeding"
	@echo "  make seed         - Run database seeders"
	@echo "  make logs         - View container logs"
	@echo "  make artisan      - Run artisan command (usage: make artisan CMD=route:list)"

# Docker Compose commands
up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart

logs:
	docker compose logs -f app

# Shell access
shell:
	docker compose exec app /bin/bash

# Testing commands
test:
	docker compose exec app php artisan test

test-auth:
	docker compose exec app php artisan test tests/Feature/Auth/

test-all:
	docker compose exec app php artisan test --coverage

test-filter:
	docker compose exec app php artisan test --filter=$(FILTER)

test-file:
	docker compose exec app php artisan test $(FILE)

# Code formatting
pint:
	docker compose exec app vendor/bin/pint --dirty

# Database commands
migrate:
	docker compose exec app php artisan migrate

fresh:
	docker compose exec app php artisan migrate:fresh --seed

seed:
	docker compose exec app php artisan db:seed

# Artisan commands
artisan:
	docker compose exec app php artisan $(CMD)

# Development helpers
install:
	docker compose exec app composer install
	docker compose exec app npm install

npm-install:
	docker compose exec app npm install

npm-build:
	docker compose exec app npm run build

build:
	docker compose build

rebuild:
	docker compose down
	docker compose build --no-cache
	docker compose up -d

