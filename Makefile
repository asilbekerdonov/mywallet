# ============================================================
#  Wallet — Laravel Docker Makefile
#  Usage: make <target>
# ============================================================

DC      = docker compose
APP     = $(DC) exec app
ARTISAN = $(APP) php artisan

.PHONY: help build up down restart logs shell \
        install migrate seed fresh test lint \
        cache-clear queue-work tinker

# ── Default ──────────────────────────────────────────────────
help:
	@echo ""
	@echo "  Wallet Laravel — Available commands"
	@echo "  ─────────────────────────────────────────────────"
	@echo "  make build          Build Docker images"
	@echo "  make up             Start all containers (detached)"
	@echo "  make down           Stop & remove containers"
	@echo "  make restart        Restart all containers"
	@echo "  make logs           Follow container logs"
	@echo ""
	@echo "  make install        composer install + npm install + build"
	@echo "  make migrate        Run php artisan migrate"
	@echo "  make seed           Run php artisan db:seed"
	@echo "  make fresh          migrate:fresh + seed (⚠ drops all tables)"
	@echo "  make test           Run PHPUnit tests"
	@echo "  make lint           Run Laravel Pint (code style fixer)"
	@echo ""
	@echo "  make shell          Open bash in app container"
	@echo "  make tinker         Open Laravel Tinker"
	@echo "  make cache-clear    Clear all Laravel caches"
	@echo "  make queue-work     Start queue worker"
	@echo ""

# ── Docker lifecycle ─────────────────────────────────────────
build:
	$(DC) build --no-cache

up:
	$(DC) up -d

down:
	$(DC) down

restart:
	$(DC) restart

logs:
	$(DC) logs -f

# ── App shell ────────────────────────────────────────────────
shell:
	$(APP) bash

tinker:
	$(ARTISAN) tinker

# ── Dependencies & assets ────────────────────────────────────
install:
	$(APP) composer install --no-interaction --optimize-autoloader
	$(APP) npm install
	$(APP) npm run build

# ── Database ─────────────────────────────────────────────────
migrate:
	$(ARTISAN) migrate --force

seed:
	$(ARTISAN) db:seed --force

fresh:
	@echo "⚠  This will DROP all tables and re-seed!"
	$(ARTISAN) migrate:fresh --seed --force

# ── Testing ──────────────────────────────────────────────────
test:
	$(APP) php artisan test --parallel

test-filter:
	# Usage: make test-filter FILTER=PaymentTest
	$(APP) php artisan test --filter=$(FILTER)

# ── Code quality ─────────────────────────────────────────────
lint:
	$(APP) ./vendor/bin/pint

# ── Cache ────────────────────────────────────────────────────
cache-clear:
	$(ARTISAN) cache:clear
	$(ARTISAN) config:clear
	$(ARTISAN) route:clear
	$(ARTISAN) view:clear

# ── Queue ────────────────────────────────────────────────────
queue-work:
	$(ARTISAN) queue:work --tries=3