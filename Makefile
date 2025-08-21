DB ?= mysql
SPA ?= 0
TOOLS ?= 0
PLATFORM ?=

PROFILES :=
ifeq ($(DB),pgsql)
PROFILES := pgsql
endif
ifeq ($(SPA),1)
PROFILES := $(if $(PROFILES),$(PROFILES),)spa
endif
ifeq ($(TOOLS),1)
PROFILES := $(if $(PROFILES),$(PROFILES),)tools
endif

COMPOSE := COMPOSE_PROFILES=$(PROFILES) COMPOSE_PROJECT_NAME=laravel-react-stack COMPOSE_DOCKER_CLI_BUILD=1 DOCKER_BUILDKIT=1 COMPOSE_PARALLEL_LIMIT=8 COMPOSE_PLATFORM=$(PLATFORM) docker compose

.PHONY: up down rebuild logs ps shell key migrate seed fresh test queue restart prune deps

up:      ; @$(COMPOSE) up -d --build && $(COMPOSE) ps
down:    ; @$(COMPOSE) down
rebuild: ; @$(COMPOSE) build --no-cache && $(COMPOSE) up -d
logs:    ; @$(COMPOSE) logs -f
ps:      ; @$(COMPOSE) ps
shell:   ; @docker compose exec app sh
deps:    ; @docker compose exec app composer install
key:     ; @docker compose exec app php artisan key:generate
migrate: ; @docker compose exec app php artisan migrate
seed:    ; @docker compose exec app php artisan db:seed
fresh:   ; @docker compose exec app php artisan migrate:fresh --seed
test:    ; @docker compose exec app php artisan test
queue:   ; @$(COMPOSE) logs -f queue
restart: ; @$(COMPOSE) restart
prune:   ; @docker system prune -f
