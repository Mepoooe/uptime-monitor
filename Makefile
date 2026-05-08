.PHONY: help up down build install fresh migrate seed shell logs

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

up: ## Start all containers
	docker compose up -d

down: ## Stop all containers
	docker compose down

build: ## Build containers
	docker compose build --no-cache

install: ## Full first install (build + up + composer + key + migrate)
	docker compose build
	docker compose up -d
	sleep 5
	docker compose exec app composer install
	docker compose exec app cp .env.example .env
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan migrate --seed
	@echo "\n✅ Done! Open http://localhost:8080"

fresh: ## Fresh migrate with seed
	docker compose exec app php artisan migrate:fresh --seed

migrate: ## Run migrations
	docker compose exec app php artisan migrate

seed: ## Run seeders
	docker compose exec app php artisan db:seed

shell: ## Open bash in app container
	docker compose exec app bash

logs: ## Tail all container logs
	docker compose logs -f

queue-restart: ## Restart queue worker
	docker compose restart queue

check: ## Manually run domain checks
	docker compose exec app php artisan domains:check

test: ## Run the test suite
	docker compose exec app php artisan test

test-filter: ## Run tests matching a filter (usage: make test-filter FILTER=AuthTest)
	docker compose exec app php artisan test --filter $(FILTER)
