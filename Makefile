.PHONY: help network up down restart logs \
	shell test test-auth test-all test-filter test-file pint migrate seed artisan \
	coach-shell coach-logs coach-test coach-up coach-down \
	build

BACKEND_DIR := backend-api
COACH_DIR := coach
PISTON_DIR := piston
NETWORK := algo-drill-network

help:
	@echo "Algo Drill — full stack"
	@echo ""
	@echo "Stack:"
	@echo "  make network     - Create shared Docker network (idempotent)"
	@echo "  make up          - Start backend-api, coach, and piston"
	@echo "  make down        - Stop all services"
	@echo "  make restart     - Restart all services (or SERVICE=app|coach|piston_api)"
	@echo "  make logs        - Follow logs (SERVICE=app|coach|piston_api, default: app)"
	@echo "  make build       - Build backend-api and coach images"
	@echo ""
	@echo "Backend:"
	@echo "  make shell       - Shell in backend container"
	@echo "  make test        - Run all tests"
	@echo "  make migrate     - Run migrations"
	@echo "  make pint        - Run Pint"
	@echo "  make artisan CMD=route:list"
	@echo ""
	@echo "Coach:"
	@echo "  make coach-shell - Shell in coach container"
	@echo "  make coach-logs  - Follow coach logs"
	@echo "  make coach-test  - Run coach tests"

network:
	@docker network inspect $(NETWORK) >/dev/null 2>&1 || docker network create $(NETWORK)

up: network
	$(MAKE) -C $(BACKEND_DIR) up
	$(MAKE) -C $(COACH_DIR) up
	cd $(PISTON_DIR) && docker compose up -d

down:
	-$(MAKE) -C $(BACKEND_DIR) down
	-$(MAKE) -C $(COACH_DIR) down
	-cd $(PISTON_DIR) && docker compose down

restart:
ifdef SERVICE
ifeq ($(SERVICE),coach)
	$(MAKE) -C $(COACH_DIR) restart
else ifeq ($(SERVICE),piston_api)
	cd $(PISTON_DIR) && docker compose restart
else
	$(MAKE) -C $(BACKEND_DIR) restart
endif
else
	$(MAKE) -C $(BACKEND_DIR) restart
	$(MAKE) -C $(COACH_DIR) restart
	cd $(PISTON_DIR) && docker compose restart
endif

logs:
ifndef SERVICE
	$(MAKE) -C $(BACKEND_DIR) logs
else ifeq ($(SERVICE),coach)
	$(MAKE) -C $(COACH_DIR) logs
else ifeq ($(SERVICE),piston_api)
	cd $(PISTON_DIR) && docker compose logs -f api
else
	cd $(BACKEND_DIR) && docker compose logs -f $(SERVICE)
endif

build:
	$(MAKE) -C $(BACKEND_DIR) build
	$(MAKE) -C $(COACH_DIR) build

# Laravel helpers
shell:
	$(MAKE) -C $(BACKEND_DIR) shell

test:
	$(MAKE) -C $(BACKEND_DIR) test

test-auth:
	$(MAKE) -C $(BACKEND_DIR) test-auth

test-all:
	$(MAKE) -C $(BACKEND_DIR) test-all

test-filter:
	$(MAKE) -C $(BACKEND_DIR) test-filter FILTER=$(FILTER)

test-file:
	$(MAKE) -C $(BACKEND_DIR) test-file FILE=$(FILE)

pint:
	$(MAKE) -C $(BACKEND_DIR) pint

migrate:
	$(MAKE) -C $(BACKEND_DIR) migrate

seed:
	$(MAKE) -C $(BACKEND_DIR) seed

artisan:
	$(MAKE) -C $(BACKEND_DIR) artisan CMD=$(CMD)

# Coach helpers
coach-up:
	$(MAKE) -C $(COACH_DIR) up

coach-down:
	$(MAKE) -C $(COACH_DIR) down

coach-shell:
	$(MAKE) -C $(COACH_DIR) shell

coach-logs:
	$(MAKE) -C $(COACH_DIR) logs

coach-test:
	$(MAKE) -C $(COACH_DIR) test
