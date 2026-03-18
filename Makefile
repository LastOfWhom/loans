.DEFAULT_GOAL = all

-include .env

DOCKER_COMPOSE = docker compose
PHP            = $(DOCKER_COMPOSE) exec php php

# База данных
export DB_HOST     ?= postgres
export DB_PORT     ?= 5432
export DB_NAME     ?= loans
export DB_USER     ?= user
export DB_PASSWORD ?= password

# Приложение
export YII_DEBUG ?= false
export YII_ENV   ?= prod

all:
	@echo "Loan API"
	@echo ""
	@echo "\033[1mНастройка\033[0m"
	@printf '    %-15s %s\n' "env"          "-- Создать .env из переменных по умолчанию"
	@echo ""
	@echo "\033[1mЖизненный цикл\033[0m"
	@printf '    %-15s %s\n' "build"        "-- Собрать образы, установить зависимости и запустить контейнеры"
	@printf '    %-15s %s\n' "rebuild"      "-- Пересобрать образы без кеша"
	@printf '    %-15s %s\n' "start"        "-- Запустить контейнеры"
	@printf '    %-15s %s\n' "stop"         "-- Остановить контейнеры (данные сохраняются)"
	@printf '    %-15s %s\n' "down"         "-- Удалить контейнеры и сети"
	@printf '    %-15s %s\n' "down-v"       "-- Удалить контейнеры, сети и данные БД"
	@echo ""
	@echo "\033[1mРазработка\033[0m"
	@printf '    %-15s %s\n' "composer"     "-- Установить зависимости Composer"
	@printf '    %-15s %s\n' "test"         "-- Запустить юнит-тесты"
	@printf '    %-15s %s\n' "migrate"      "-- Применить миграции БД"
	@printf '    %-15s %s\n' "migrate-down" "-- Откатить последнюю миграцию"
	@printf '    %-15s %s\n' "shell"        "-- Открыть консоль внутри контейнера php"
	@printf '    %-15s %s\n' "shell-db"     "-- Открыть psql внутри контейнера postgres"
	@printf '    %-15s %s\n' "logs"         "-- Логи всех сервисов (follow)"
	@printf '    %-15s %s\n' "ps"           "-- Статус контейнеров"

env:
	@echo "# База данных"             > .env
	@echo "DB_HOST=$(DB_HOST)"        >> .env
	@echo "DB_PORT=$(DB_PORT)"        >> .env
	@echo "DB_NAME=$(DB_NAME)"        >> .env
	@echo "DB_USER=$(DB_USER)"        >> .env
	@echo "DB_PASSWORD=$(DB_PASSWORD)" >> .env
	@echo ""                          >> .env
	@echo "# Приложение"              >> .env
	@echo "YII_DEBUG=$(YII_DEBUG)"    >> .env
	@echo "YII_ENV=$(YII_ENV)"        >> .env

build:
	$(DOCKER_COMPOSE) up -d --build
	$(MAKE) composer
	$(MAKE) migrate

rebuild:
	$(DOCKER_COMPOSE) build --no-cache
	$(DOCKER_COMPOSE) up -d

start:
	$(DOCKER_COMPOSE) up -d

stop:
	$(DOCKER_COMPOSE) stop

down:
	$(DOCKER_COMPOSE) down

down-v:
	$(DOCKER_COMPOSE) down -v

composer:
	$(DOCKER_COMPOSE) exec php composer install --no-interaction --prefer-dist --optimize-autoloader

test:
	$(PHP) vendor/bin/phpunit --testdox

migrate:
	$(PHP) yii migrate --interactive=0

migrate-down:
	$(PHP) yii migrate/down 1 --interactive=0

shell:
	$(DOCKER_COMPOSE) exec php sh

shell-db:
	$(DOCKER_COMPOSE) exec postgres psql -U $(DB_USER) -d $(DB_NAME)

logs:
	$(DOCKER_COMPOSE) logs -f

ps:
	$(DOCKER_COMPOSE) ps
