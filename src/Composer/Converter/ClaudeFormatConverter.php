<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Composer\Converter;

use Symkit\BundleAiKit\Composer\Contract\ContentConverterInterface;

final readonly class ClaudeFormatConverter implements ContentConverterInterface
{
    public function convert(string $mdcContent): string
    {
        return FrontmatterConverter::toClaudeFormat($mdcContent);
    }
}
