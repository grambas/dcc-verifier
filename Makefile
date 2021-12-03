.DEFAULT_GOAL := help

help:
	@echo ""
	@echo "Available tasks:"
	@echo "    test    Run all tests and generate coverage"
	@echo "    fixer    Run only php cs fixer"
	@echo "    unit    Run unit tests and generate coverage"
	@echo "    static  Run static analysis"
	@echo "    vendor  Install dependencies"
	@echo "    clean   Remove vendor and composer.lock"
	@echo ""

vendor: $(wildcard composer.lock)
	composer install --prefer-dist

fixer: vendor
	vendor/bin/php-cs-fixer fix

unit: vendor
	 XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-text --coverage-clover=./.phpunit.cache/coverage.xml --coverage-html=./.phpunit.cache/html-coverage/

static: vendor
	vendor/bin/phpstan analyse src --level 8

test: fixer unit static

clean:
	rm -rf ./vendor
	rm -rf ./composer.lock
	rm -rf ./.phplint-cache
	rm -rf ./.phpunit.cache
	rm -rf ./.php-cs-fixer.cache

.PHONY: help fixer unit watch test travis clean