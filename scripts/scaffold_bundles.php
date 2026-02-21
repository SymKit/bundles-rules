#!/usr/bin/env php
<?php
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
    'src/Service',
    'src/EventSubscriber',
    'config',
    'templates',
    'tests/Unit',
    'tests/Integration',
    'tests/Functional',
    'translations',
    'docs',
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
$composerRequire = [
    'php' => '>=8.2',
    'symfony/framework-bundle' => '^7.0',
    'symfony/dependency-injection' => '^7.0',
    'symfony/http-kernel' => '^7.0',
];

$composerRequireDev = [
    'symfony/phpunit-bridge' => '^7.0',
    'nyholm/symfony-bundle-test' => '^3.0',
    'phpunit/phpunit' => '^10.0',
];

$composerSuggest = [];

if ($withDoctrine) {
    $composerRequire['doctrine/orm'] = '^3.0';
    $composerRequire['doctrine/doctrine-bundle'] = '^2.11';
}

if ($withUx) {
    $composerRequire['symfony/stimulus-bundle'] = '^2.0';
    $composerRequire['symfony/asset-mapper'] = '^7.0';
    $composerRequire['symfony/ux-twig-component'] = '^2.0';
    $composerSuggest['twig/twig'] = 'Required for template rendering (^3.0)';
}

$composer = [
    'name' => $packageName,
    'type' => 'symfony-bundle',
    'description' => "TODO: Describe what {$bundleName} does",
    'license' => 'MIT',
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
            'require' => '^7.0',
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

$bundleClass = <<<PHP
<?php

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

    public function prependExtension(
        ContainerConfigurator \$container,
        ContainerBuilder \$builder
    ): void {
{$prependBody}
    }
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

A Symfony 7+ bundle for TODO.

## Requirements

- PHP 8.2+
- Symfony 7.0+

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
$gitignore = <<<GITIGNORE
/vendor/
/.phpunit.cache/
/composer.lock
*.cache
GITIGNORE;

file_put_contents("{$baseDir}/.gitignore", $gitignore);
echo "  Created .gitignore\n";

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
echo "  2. composer install\n";
echo "  3. Edit src/{$bundleName}.php to define your configuration\n";
echo "  4. Add services in config/services.xml\n";
echo "  5. Run tests: vendor/bin/phpunit\n";
