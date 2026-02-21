<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Composer\Converter;

use Symkit\BundleAiKit\Composer\Contract\ContentConverterInterface;

/**
 * No-op converter: returns content unchanged (used when target format equals source).
 */
final readonly class IdentityConverter implements ContentConverterInterface
{
    public function convert(string $mdcContent): string
    {
        return $mdcContent;
    }
}
