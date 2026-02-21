<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Composer\Config;

use Symkit\BundleAiKit\Composer\Contract\ContentConverterInterface;

/**
 * Value object: destination paths and conversion strategy for one AI editor.
 */
final readonly class EditorConfig
{
    public function __construct(
        public string $rulesDir,
        public string $skillsDir,
        public string $fileExtension,
        public ?ContentConverterInterface $contentConverter,
    ) {
    }
}
