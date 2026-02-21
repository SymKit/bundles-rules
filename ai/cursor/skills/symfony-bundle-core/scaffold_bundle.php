#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Symfony Bundle Scaffolding Script
 *
 * Usage: php scaffold_bundle.php <vendor> <name> [--with-doctrine] [--with-ux] [--with-flex]
 *
 * Example: php scaffold_bundle.php Acme Blog --with-doctrine --with-ux --with-flex
 */

$vendor = $argv[1] ?? null;
$name = $argv[2] ?? null;
$withDoctrine = in_array('--with-doctrine', $argv);
$withUx = in_array('--with-ux', $argv);
$withFlex = in_array('--with-flex', $argv);

if (!$vendor || !$name) {
    echo "Usage: php scaffold_bundle.php <Vendor> <Name> [--with-doctrine] [--with-ux] [--with-flex]\n";
    echo "Example: php scaffold_bundle.php Acme Blog --with-doctrine --with-ux\n";
    exit(1);
}

$vendorLower = strtolower($vendor);
$nameLower = strtolower($name);
$bundleName = "{$vendor}{$name}Bundle";
$namespace = "{$vendor}\\{$name}Bundle";
$alias = "{$vendorLower}_{$nameLower}";
$packageName = "{$vendorLower}/{$nameLower}-bundle";
$baseDir = getcwd() . "/{$vendorLower}-{$nameLower}-bundle";

echo "Scaffolding {$bundleName} ({$namespace})...\n";

// Directory structure
$dirs = [
    'src',
    'src/Controller',
    'src/Contract',
    'src/DependencyInjection/CompilerPass',
    'src/Event',
    'src/Service',
    'src/EventSubscriber',
    'config',
    'templates',
    'tests/Unit',
    'tests/Integration',
    'tests/Functional',
    'translations',
    'docs',
    '.github/workflows',
];

if ($withDoctrine) {
    $dirs[] = 'src/Entity';
    $dirs[] = 'src/Repository';
    $dirs[] = 'config/doctrine';
}

if ($withUx) {
    $dirs[] = 'assets/controllers';
    $dirs[] = 'assets/dist';
    $dirs[] = 'assets/dist/styles';
    $dirs[] = 'assets/styles';
    $dirs[] = 'src/Twig/Components';
    $dirs[] = 'templates/components';
}

foreach ($dirs as $dir) {
    $path = "{$baseDir}/{$dir}";
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        echo "  Created {$dir}/\n";
    }
}

// --- composer.json ---
$sfVersion = '^7.0 || ^8.0';

$composerRequire = [
    'php' => '>=8.2',
    'symfony/config' => $sfVersion,
    'symfony/dependency-injection' => $sfVersion,
    'symfony/framework-bundle' => $sfVersion,
    'symfony/http-kernel' => $sfVersion,
];

$composerRequireDev = [
    'deptrac/deptrac-src' => '^2.0',
    'friendsofphp/php-cs-fixer' => '^3.0',
    'infection/infection' => '^0.29',
    'nyholm/symfony-bundle-test' => '^3.0',
    'phpro/grumphp' => '^2.0',
    'phpstan/phpstan' => '^2.0',
    'phpunit/phpunit' => '^10.0 || ^11.0',
    'symfony/phpunit-bridge' => $sfVersion,
];

$composerSuggest = [];

if ($withDoctrine) {
    $composerRequire['doctrine/orm'] = '^3.0';
    $composerRequire['doctrine/doctrine-bundle'] = '^2.11';
}

if ($withUx) {
    $composerRequire['symfony/stimulus-bundle'] = '^2.0';
    $composerRequire['symfony/asset-mapper'] = $sfVersion;
    $composerRequire['symfony/ux-twig-component'] = '^2.0';
    $composerSuggest['twig/twig'] = 'Required for template rendering (^3.0)';
}

$composer = [
    'name' => $packageName,
    'type' => 'symfony-bundle',
    'description' => "TODO: Describe what {$bundleName} does",
    'license' => 'MIT',
    'authors' => [
        ['name' => 'TODO', 'email' => 'TODO'],
    ],
    'homepage' => 'https://github.com/TODO/TODO',
    'keywords' => ['symfony', 'bundle'],
    'require' => $composerRequire,
    'require-dev' => $composerRequireDev,
    'autoload' => [
        'psr-4' => [
            "{$namespace}\\" => 'src/',
        ],
    ],
    'autoload-dev' => [
        'psr-4' => [
            "{$namespace}\\Tests\\" => 'tests/',
        ],
    ],
    'extra' => [
        'symfony' => [
            'require' => $sfVersion,
        ],
    ],
];

if (!empty($composerSuggest)) {
    $composer['suggest'] = $composerSuggest;
}

file_put_contents(
    "{$baseDir}/composer.json",
    json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
);
echo "  Created composer.json\n";

// --- AbstractBundle class ---
$needsPrepend = $withUx || $withDoctrine;

$prependParts = [];

if ($withUx) {
    $prependParts[] = <<<PHP
        // Register assets with AssetMapper
        if (\$this->isAssetMapperAvailable(\$builder)) {
            \$builder->prependExtensionConfig('framework', [
                'asset_mapper' => [
                    'paths' => [
                        __DIR__ . '/../assets/dist' => '@{$vendorLower}/{$nameLower}-bundle',
                    ],
                ],
            ]);
        }

        // Register Twig Component namespace
        \$builder->prependExtensionConfig('twig_component', [
            'defaults' => [
                '{$namespace}\\\\Twig\\\\Components\\\\' => [
                    'template_directory' => '@{$vendor}{$name}/components',
                    'name_prefix' => '{$vendor}',
                ],
            ],
        ]);
PHP;
}

if ($withDoctrine) {
    $prependParts[] = <<<PHP
        // Register Doctrine mappings
        \$builder->prependExtensionConfig('doctrine', [
            'orm' => [
                'mappings' => [
                    '{$bundleName}' => [
                        'type' => 'xml',
                        'dir' => __DIR__ . '/../config/doctrine',
                        'prefix' => '{$namespace}\\\\Entity',
                        'alias' => '{$vendor}{$name}',
                        'is_bundle' => false,
                    ],
                ],
            ],
        ]);
PHP;
}

$prependBody = implode("\n\n", $prependParts);

$assetMapperHelper = $withUx ? <<<'PHP'

    private function isAssetMapperAvailable(ContainerBuilder $builder): bool
    {
        if (!interface_exists(\Symfony\Component\AssetMapper\AssetMapperInterface::class)) {
            return false;
        }
        $dependencies = $builder->getExtensionConfig('framework');
        return !isset($dependencies[0]['asset_mapper'])
            || $dependencies[0]['asset_mapper'] !== false;
    }
PHP : '';

$prependMethod = '';
if ($needsPrepend) {
    $prependMethod = <<<PHP

    public function prependExtension(
        ContainerConfigurator \$container,
        ContainerBuilder \$builder
    ): void {
{$prependBody}
    }
PHP;
}

$bundleClass = <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace};

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class {$bundleName} extends AbstractBundle
{
    public function configure(DefinitionConfigurator \$definition): void
    {
        \$definition->rootNode()
            ->children()
                // TODO: Define your configuration tree
                ->booleanNode('enabled')
                    ->defaultTrue()
                ->end()
            ->end()
        ;
    }

    public function loadExtension(
        array \$config,
        ContainerConfigurator \$container,
        ContainerBuilder \$builder
    ): void {
        \$container->import('../config/services.xml');

        \$container->parameters()
            ->set('{$alias}.enabled', \$config['enabled'])
        ;
    }
{$prependMethod}
{$assetMapperHelper}
}

PHP;

file_put_contents("{$baseDir}/src/{$bundleName}.php", $bundleClass);
echo "  Created src/{$bundleName}.php\n";

// --- services.xml ---
$servicesXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<container xmlns="http://symfony.com/schema/dic/services"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://symfony.com/schema/dic/services
        https://symfony.com/schema/dic/services/services-1.0.xsd">

    <services>
        <defaults autowire="true" autoconfigure="true" public="false" />

        <!-- TODO: Register your services here -->
        <!-- <service id="{$alias}.example_service"
                 class="{$namespace}\\Service\\ExampleService" /> -->
    </services>
</container>
XML;

file_put_contents("{$baseDir}/config/services.xml", $servicesXml);
echo "  Created config/services.xml\n";

// --- routes.yaml ---
$routesYaml = <<<YAML
# {$bundleName} routes
# TODO: Define your bundle routes
# {$alias}_index:
#     path: /
#     controller: {$namespace}\\Controller\\DefaultController::index
#     methods: [GET]
YAML;

file_put_contents("{$baseDir}/config/routes.yaml", $routesYaml);
echo "  Created config/routes.yaml\n";

// --- Translation file ---
$xliff = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
    <file source-language="en" datatype="plaintext" original="{$bundleName}">
        <body>
            <!-- TODO: Add your translation units -->
            <trans-unit id="greeting">
                <source>greeting</source>
                <target>Hello from {$bundleName}!</target>
            </trans-unit>
        </body>
    </file>
</xliff>
XML;

file_put_contents("{$baseDir}/translations/{$bundleName}.en.xlf", $xliff);
echo "  Created translations/{$bundleName}.en.xlf\n";

// --- Integration test ---
$testClass = <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace}\\Tests\\Integration;

use {$namespace}\\{$bundleName};
use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class BundleInitializationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function createKernel(array \$options = []): KernelInterface
    {
        /** @var TestKernel \$kernel */
        \$kernel = parent::createKernel(\$options);
        \$kernel->addTestBundle({$bundleName}::class);
        \$kernel->addTestConfig(function (\$container) {
            \$container->loadFromExtension('{$alias}', [
                'enabled' => true,
            ]);
        });
        \$kernel->handleOptions(\$options);

        return \$kernel;
    }

    public function testBundleBoots(): void
    {
        self::bootKernel();
        \$this->assertNotNull(self::getContainer());
    }

    // TODO: Add service assertions
    // public function testServiceIsRegistered(): void
    // {
    //     self::bootKernel();
    //     \$this->assertTrue(self::getContainer()->has('{$alias}.example_service'));
    // }
}

PHP;

file_put_contents("{$baseDir}/tests/Integration/BundleInitializationTest.php", $testClass);
echo "  Created tests/Integration/BundleInitializationTest.php\n";

// --- phpunit.xml.dist ---
$phpunitXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         failOnRisky="true"
         failOnWarning="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
        <testsuite name="Functional">
            <directory>tests/Functional</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>src</directory>
        </include>
    </source>
</phpunit>
XML;

file_put_contents("{$baseDir}/phpunit.xml.dist", $phpunitXml);
echo "  Created phpunit.xml.dist\n";

// --- docs/index.md ---
$docs = <<<MD
# {$bundleName}

## Installation

```bash
composer require {$packageName}
```

## Configuration

```yaml
# config/packages/{$alias}.yaml
{$alias}:
    enabled: true
```

## Usage

TODO: Document usage.
MD;

file_put_contents("{$baseDir}/docs/index.md", $docs);
echo "  Created docs/index.md\n";

// --- README.md ---
$readme = <<<MD
# {$bundleName}

A Symfony 7+/8+ bundle for TODO.

## Requirements

- PHP 8.2+
- Symfony 7.0+ or 8.0+

## Installation

```bash
composer require {$packageName}
```

## Documentation

See [docs/index.md](docs/index.md).

## License

MIT
MD;

file_put_contents("{$baseDir}/README.md", $readme);
echo "  Created README.md\n";

// --- .gitignore ---
$gitignore = <<<'GITIGNORE'
/vendor/
/.phpunit.cache/
/.php-cs-fixer.cache
/.phpstan-cache/
/composer.lock
/infection.log
/infection-summary.log
*.cache
GITIGNORE;

file_put_contents("{$baseDir}/.gitignore", $gitignore);
echo "  Created .gitignore\n";

// --- LICENSE ---
$year = date('Y');
$license = <<<LICENSE
MIT License

Copyright (c) {$year} {$vendor}

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
LICENSE;

file_put_contents("{$baseDir}/LICENSE", $license);
echo "  Created LICENSE\n";

// --- CHANGELOG.md ---
$changelog = <<<'MD'
# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Initial bundle scaffolding
MD;

file_put_contents("{$baseDir}/CHANGELOG.md", $changelog);
echo "  Created CHANGELOG.md\n";

// --- .editorconfig ---
$editorconfig = <<<'EDITORCONFIG'
root = true

[*]
charset = utf-8
end_of_line = lf
insert_final_newline = true
trim_trailing_whitespace = true

[*.php]
indent_style = space
indent_size = 4

[*.{yaml,yml,json,js,ts,css}]
indent_style = space
indent_size = 2

[*.xml]
indent_style = space
indent_size = 4

[Makefile]
indent_style = tab
EDITORCONFIG;

file_put_contents("{$baseDir}/.editorconfig", $editorconfig);
echo "  Created .editorconfig\n";

// --- Makefile ---
$makefile = ".PHONY: help install test phpstan cs-fix cs-check infection deptrac quality security-check lint ci\n";
$makefile .= "\n";
$makefile .= "help: ## Show available commands\n";
$makefile .= "\t@grep -E '^[a-zA-Z_-]+:.*?## .*\$\$' \$(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = \":.*?## \"}; {printf \"\\033[36m%-20s\\033[0m %s\\n\", \$\$1, \$\$2}'\n";
$makefile .= "\n";
$makefile .= "install: ## Install dependencies\n";
$makefile .= "\tcomposer install\n";
$makefile .= "\n";
$makefile .= "test: ## Run PHPUnit tests\n";
$makefile .= "\tvendor/bin/phpunit\n";
$makefile .= "\n";
$makefile .= "phpstan: ## Static analysis (level 9)\n";
$makefile .= "\tvendor/bin/phpstan analyse --memory-limit=512M\n";
$makefile .= "\n";
$makefile .= "cs-fix: ## Fix code style\n";
$makefile .= "\tvendor/bin/php-cs-fixer fix\n";
$makefile .= "\n";
$makefile .= "cs-check: ## Check code style (dry run)\n";
$makefile .= "\tvendor/bin/php-cs-fixer fix --dry-run --diff\n";
$makefile .= "\n";
$makefile .= "infection: ## Mutation testing\n";
$makefile .= "\tvendor/bin/infection --only-covered --show-mutations --threads=max --min-msi=70\n";
$makefile .= "\n";
$makefile .= "deptrac: ## Architecture check\n";
$makefile .= "\tvendor/bin/deptrac analyse\n";
$makefile .= "\n";
$makefile .= "security-check: ## Security audit\n";
$makefile .= "\tcomposer audit\n";
$makefile .= "\n";
$makefile .= "lint: ## Lint config files\n";
$makefile .= "\t@test -d config && find config -name '*.xml' -exec xmllint --noout {} + 2>/dev/null || true\n";
$makefile .= "\n";
$makefile .= "quality: cs-check phpstan deptrac lint test infection ## Full quality pipeline\n";
$makefile .= "\n";
$makefile .= "ci: security-check quality ## Full CI pipeline\n";

file_put_contents("{$baseDir}/Makefile", $makefile);
echo "  Created Makefile\n";

// --- phpstan.neon.dist ---
$phpstanNeon = <<<'NEON'
parameters:
    level: 9
    paths:
        - src/
    tmpDir: .phpstan-cache
NEON;

file_put_contents("{$baseDir}/phpstan.neon.dist", $phpstanNeon);
echo "  Created phpstan.neon.dist\n";

// --- .php-cs-fixer.dist.php ---
$csFixer = <<<'PHP'
<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PHP82Migration' => true,
        'declare_strict_types' => true,
        'native_function_invocation' => [
            'include' => ['@compiler_optimized'],
            'scope' => 'namespaced',
            'strict' => true,
        ],
        'trailing_comma_in_multiline' => [
            'elements' => ['arguments', 'parameters', 'match', 'arrays'],
        ],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_functions' => false,
            'import_constants' => false,
        ],
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;
PHP;

file_put_contents("{$baseDir}/.php-cs-fixer.dist.php", $csFixer . "\n");
echo "  Created .php-cs-fixer.dist.php\n";

// --- grumphp.yml ---
$grumphp = <<<'YAML'
grumphp:
    tasks:
        phpstan:
            config: phpstan.neon.dist
            memory_limit: 512M
        phpcsfixer:
            config: .php-cs-fixer.dist.php
        phpunit:
            config: phpunit.xml.dist
        composer_audit: ~
YAML;

file_put_contents("{$baseDir}/grumphp.yml", $grumphp . "\n");
echo "  Created grumphp.yml\n";

// --- infection.json5 ---
$infection = <<<'JSON5'
{
    "$schema": "https://raw.githubusercontent.com/infection/infection/0.29.0/resources/schema.json",
    "source": { "directories": ["src/"] },
    "logs": {
        "text": "infection.log",
        "summary": "infection-summary.log"
    },
    "minMsi": 70,
    "minCoveredMsi": 80,
    "threads": "max",
    "testFramework": "phpunit",
    "testFrameworkOptions": "--testsuite=Unit"
}
JSON5;

file_put_contents("{$baseDir}/infection.json5", $infection . "\n");
echo "  Created infection.json5\n";

// --- deptrac.yaml ---
$deptrac = <<<'YAML'
deptrac:
    paths:
        - ./src
    layers:
        - name: Contract
          collectors:
            - type: directory
              value: src/Contract/.*
        - name: Entity
          collectors:
            - type: directory
              value: src/Entity/.*
        - name: Event
          collectors:
            - type: directory
              value: src/Event/.*
        - name: Repository
          collectors:
            - type: directory
              value: src/Repository/.*
        - name: Service
          collectors:
            - type: directory
              value: src/Service/.*
        - name: Controller
          collectors:
            - type: directory
              value: src/Controller/.*
        - name: DependencyInjection
          collectors:
            - type: directory
              value: src/DependencyInjection/.*
    ruleset:
        Contract: []
        Entity: [Contract]
        Event: [Contract, Entity]
        Repository: [Contract, Entity]
        Service: [Contract, Entity, Repository, Event]
        Controller: [Contract, Service, Entity]
        DependencyInjection: [Contract, Service, Entity, Repository, Event, Controller]
YAML;

file_put_contents("{$baseDir}/deptrac.yaml", $deptrac . "\n");
echo "  Created deptrac.yaml\n";

// --- .github/workflows/ci.yml ---
$ciYml = <<<'YAML'
name: CI

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  security:
    name: Security Audit
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          coverage: none
      - run: composer install --no-interaction --prefer-dist
      - run: composer audit

  cs:
    name: Code Style
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          coverage: none
      - run: composer install --no-interaction --prefer-dist
      - run: vendor/bin/php-cs-fixer fix --dry-run --diff

  phpstan:
    name: PHPStan
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          coverage: none
      - run: composer install --no-interaction --prefer-dist
      - run: vendor/bin/phpstan analyse --memory-limit=512M

  deptrac:
    name: Architecture (Deptrac)
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          coverage: none
      - run: composer install --no-interaction --prefer-dist
      - run: vendor/bin/deptrac analyse

  tests:
    name: Tests (PHP ${{ matrix.php }} / Symfony ${{ matrix.symfony }})
    runs-on: ubuntu-latest
    strategy:
      fail-fast: false
      matrix:
        include:
          - php: '8.2'
            symfony: '7.0'
          - php: '8.3'
            symfony: '7.0'
          - php: '8.3'
            symfony: '8.0'
          - php: '8.4'
            symfony: '8.0'
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          coverage: xdebug
      - name: Install Symfony ${{ matrix.symfony }}
        run: |
          composer config extra.symfony.require "^${{ matrix.symfony }}"
          composer update --no-interaction --prefer-dist
      - run: vendor/bin/phpunit --coverage-clover=coverage.xml

  infection:
    name: Mutation Testing
    runs-on: ubuntu-latest
    needs: [tests]
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          coverage: xdebug
      - run: composer install --no-interaction --prefer-dist
      - run: vendor/bin/infection --only-covered --show-mutations --threads=max --min-msi=70
YAML;

file_put_contents("{$baseDir}/.github/workflows/ci.yml", $ciYml . "\n");
echo "  Created .github/workflows/ci.yml\n";

// --- UX-specific files ---
if ($withUx) {
    $packageJson = json_encode([
        'name' => "@{$vendorLower}/{$nameLower}-bundle",
        'description' => "Stimulus controllers for {$bundleName}",
        'symfony' => [
            'controllers' => new \stdClass(),
            'importmap' => new \stdClass(),
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    file_put_contents("{$baseDir}/assets/package.json", $packageJson . "\n");
    echo "  Created assets/package.json\n";
}

// --- Flex recipe ---
if ($withFlex) {
    $flexDir = "{$baseDir}/flex-recipe/{$vendorLower}/{$nameLower}-bundle/1.0";
    mkdir($flexDir . '/config/packages', 0755, true);

    $manifest = json_encode([
        'bundles' => [
            "{$namespace}\\{$bundleName}" => ['all'],
        ],
        'copy-from-recipe' => [
            'config/' => '%CONFIG_DIR%/',
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    file_put_contents("{$flexDir}/manifest.json", $manifest . "\n");

    $defaultConfig = "{$alias}:\n    enabled: true\n    # TODO: Add default configuration\n";
    file_put_contents("{$flexDir}/config/packages/{$alias}.yaml", $defaultConfig);

    echo "  Created flex-recipe/ (ready for recipes-contrib PR)\n";
}

echo "\nDone! Bundle scaffolded at: {$baseDir}\n";
echo "Next steps:\n";
echo "  1. cd {$vendorLower}-{$nameLower}-bundle\n";
echo "  2. make install\n";
echo "  3. Edit src/{$bundleName}.php to define your configuration\n";
echo "  4. Add services in config/services.xml\n";
echo "  5. make quality\n";
