.PHONY: cs-fix cs-check phpstan test deptrac infection security-check quality ci

cs-fix:
	vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --allow-risky=yes

cs-check:
	vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --allow-risky=yes --dry-run --diff

phpstan:
	vendor/bin/phpstan analyse --configuration=phpstan.neon.dist --memory-limit=-1

test:
	vendor/bin/phpunit --configuration=phpunit.xml.dist

deptrac:
	vendor/bin/deptrac analyse --config-file=deptrac.yaml

infection:
	vendor/bin/infection --configuration=infection.json5 --only-covered --threads=max --min-msi=70 --min-covered-msi=70

security-check:
	composer audit --abandoned=report

quality: cs-check phpstan deptrac test infection

ci: security-check quality
