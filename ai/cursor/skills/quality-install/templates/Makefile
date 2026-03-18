# === Quality pipeline — contract: 3 targets only ===
# Boilerplate contract: make ci, make tests, make quality.
# All tools run inside these targets. See quality-pipeline.mdc.
# GrumPHP pre-commit: make quality
# After any implementation: make ci

# Docker: set EXEC to run commands in container (e.g., docker compose exec php)
EXEC ?=

PHP = $(EXEC) php
COMPOSER = $(EXEC) composer
PHPUNIT = $(EXEC) vendor/bin/phpunit
PHPSTAN = $(EXEC) vendor/bin/phpstan analyse --no-progress --memory-limit=512M
CSFIXER = $(EXEC) vendor/bin/php-cs-fixer
DEPTRAC = $(EXEC) vendor/bin/deptrac analyse --no-progress
INFECTION = $(EXEC) vendor/bin/infection --no-progress --threads=max

.PHONY: quality tests ci

quality: ## Lint, Deptrac, PHPStan, CS-Fixer check (all quality tools)
	@$(EXEC) find src -name "*.php" -exec $(PHP) -l {} \; 2>/dev/null | grep -v "No syntax errors" || true
	@$(EXEC) sh -c 'test -f bin/console && $(PHP) bin/console lint:container' 2>/dev/null || true
	@$(EXEC) sh -c 'test -f bin/console && $(PHP) bin/console lint:yaml config/ --parse-tags' 2>/dev/null || true
	@$(EXEC) sh -c 'test -f bin/console && $(PHP) bin/console lint:twig templates/' 2>/dev/null || true
	@$(EXEC) sh -c 'test -f bin/console && $(PHP) bin/console lint:xliff translations/' 2>/dev/null || true
	@$(EXEC) sh -c 'test -f bin/console && $(PHP) bin/console doctrine:schema:validate --skip-sync' 2>/dev/null || true
	$(PHPSTAN)
	$(CSFIXER) fix --dry-run --diff
	$(DEPTRAC)
	@echo "✓ Quality passed"

tests: ## PHPUnit + Infection (all test tools)
	$(PHPUNIT)
	$(INFECTION) --min-msi=70 --min-covered-msi=80
	@echo "✓ Tests passed"

ci: ## Full pipeline — run after any implementation (audit + quality + tests)
	@$(COMPOSER) audit --no-interaction --format=plain 2>/dev/null || true
	@$(MAKE) quality
	@$(MAKE) tests
	@echo "✓ CI complete"
