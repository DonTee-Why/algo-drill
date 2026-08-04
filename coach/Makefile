.PHONY: help up down restart logs shell test build rebuild

help:
	@echo "Available commands:"
	@echo "  make up       - Start coach container"
	@echo "  make down     - Stop coach container"
	@echo "  make restart  - Restart coach container"
	@echo "  make logs     - View coach logs"
	@echo "  make shell    - Open shell in coach container"
	@echo "  make test     - Run coach tests"
	@echo "  make build    - Build coach image"
	@echo "  make rebuild  - Rebuild coach image without cache"

up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart

logs:
	docker compose logs -f coach

shell:
	docker compose exec coach /bin/sh

test:
	docker compose exec coach pytest

build:
	docker compose build

rebuild:
	docker compose down
	docker compose build --no-cache
	docker compose up -d
