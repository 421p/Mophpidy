DOCKER_COMPOSE?=docker-compose -f docker-compose.yml -f docker-compose.local.yml
RUN=$(DOCKER_COMPOSE) run --rm php
EXEC?=$(DOCKER_COMPOSE) exec bot
CONSOLE=bin/console
COMPOSER=$(EXEC) composer
COMPOSER_REQUIRE=$(COMPOSER) require
COMPOSER_REQUIRE_DEV=$=$(COMPOSER_REQUIRE) --dev
PHPCSFIXER?=$(EXEC) vendor/bin/php-cs-fixer
BEHAT_ARGS?=-vvv
PHPUNIT_ARGS?=-v
PHPSPEC_ARGS?=--format=pretty
ARGS = $(filter-out $@,$(MAKECMDGOALS))

##
## Helpers
##---------------------------------------------------------------------------

console:
	$(EXEC) bash

cs:
	$(PHPCSFIXER) fix . --rules=@Symfony

logs:
	$(DOCKER_COMPOSE) logs $(ARGS)

logsf:
	$(DOCKER_COMPOSE) logs -f
##
## Docker compose
##---------------------------------------------------------------------------

image:
	docker build . -t 421p/mophpidy -f build/amd/Dockerfile

image-nginx:
	docker build . -t 421p/mophpidy-nginx -f build/nginx/amd/Dockerfile

up:
	$(DOCKER_COMPOSE) up -d --remove-orphans

upf:
	$(DOCKER_COMPOSE) up --remove-orphans

start: build up

stop:                                                                                                  ## Remove docker containers
	$(DOCKER_COMPOSE) kill
	$(DOCKER_COMPOSE) rm -v --force

# Rules from files
vendor: composer.lock
	$(COMPOSER) install -n

composer.lock: composer.json
	@echo compose.lock is not up to date.