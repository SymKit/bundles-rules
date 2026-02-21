<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
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
        'single_line_throw' => false,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;
