.PHONY: help test test-auth test-all test-filter test-file pint up down restart shell migrate fresh seed logs

# Default target
help:
	@echo "Available commands:"
	@echo "  make up          - Start backend-api containers (use root make up for full stack)"
	@echo "  make down        - Stop backend-api containers"
	@echo "  make restart     - Restart Docker containers"
	@echo "  make start       - Start full stack via root Makefile"
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

# Docker Compose commands (backend-api stack only; use root `make up` for full stack)
up:
	docker compose up -d
	docker compose exec app sh -c 'until ./node_modules/.bin/vite --version >/dev/null 2>&1; do echo "Waiting for entrypoint npm install..."; sleep 2; done'
	docker compose exec app npm run build

down:
	docker compose down

down-v:
	docker compose down -v

start:
	cd .. && $(MAKE) up

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

migrate-fresh:
	docker compose exec app php artisan migrate:fresh

migrate-fresh-seed:
	docker compose exec app php artisan migrate:fresh --seed

seed:
	docker compose exec app php artisan db:seed

# Artisan commands
artisan:
	docker compose exec app php artisan $(CMD)

artisan-make:
	docker compose exec app php artisan make:$(TYPE) $(NAME)

# Development helpers
install:
	docker compose exec app composer install
	docker compose exec app npm install

npm-install:
	docker compose exec app npm install

npm-reinstall:
	docker compose stop app
	docker rm -v algo-drill-app
	docker compose up -d app
	docker compose exec app sh -c 'until ./node_modules/.bin/vite --version >/dev/null 2>&1; do echo "Waiting for npm install..."; sleep 2; done'

npm-build:
	docker compose exec app npm run build

npm-build-watch:
	docker compose exec app npm run build:watch

npm-dev:
	docker compose exec app npm run dev

build:
	docker compose build

rebuild:
	docker compose down
	docker compose build --no-cache
	docker compose up -d

