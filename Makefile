# Docker helpers for the hackathon app.
# Usage: `make up`, `make down`, `make build`, etc.

.PHONY: up down build rebuild restart logs shell migrate fresh test ps

## Start the stack (app + MySQL) in the background, building if needed
up:
	docker compose up -d --build

## Stop and remove the containers (keeps the MySQL data volume)
down:
	docker compose down

## Build/rebuild the images without starting
build:
	docker compose build

## Rebuild from scratch (no cache) and start
rebuild:
	docker compose build --no-cache
	docker compose up -d

## Restart the running containers
restart:
	docker compose restart

## Follow the app logs (Ctrl-C to stop)
logs:
	docker compose logs -f app

## Open a shell inside the app container
shell:
	docker compose exec app bash

## Run database migrations inside the container
migrate:
	docker compose exec app php artisan migrate --force

## Drop and re-run all migrations
fresh:
	docker compose exec app php artisan migrate:fresh --force

## Run the test suite inside the container
test:
	docker compose exec app php artisan test

## Show container status
ps:
	docker compose ps
