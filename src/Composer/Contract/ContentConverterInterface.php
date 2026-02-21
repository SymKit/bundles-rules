<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Composer\Contract;

/**
 * Converts Cursor .mdc content to a target editor format.
 */
interface ContentConverterInterface
{
    public function convert(string $mdcContent): string;
}
