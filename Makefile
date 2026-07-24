.PHONY: install qa test phpstan cs-check deptrac platform docker-build docker-qa docker-integration

install:
	composer install --prefer-dist --no-interaction

platform:
	php apps/console/bin/platform

test:
	composer test

phpstan:
	composer phpstan

cs-check:
	composer cs-check

deptrac:
	composer deptrac

qa:
	composer qa

docker-build:
	docker compose build php

docker-qa:
	docker compose run --rm --no-deps php sh -lc 'composer install --prefer-dist --no-interaction && composer qa && php apps/console/bin/platform'

docker-integration:
	docker compose run --rm php sh -lc 'composer install --prefer-dist --no-interaction && vendor/bin/phpunit --colors=always --testsuite Integration'
