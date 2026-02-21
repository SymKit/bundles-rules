<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Tests\Unit\Converter;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symkit\BundleAiKit\Composer\Converter\AntigravityFormatConverter;

final class AntigravityFormatConverterTest extends TestCase
{
    #[Test]
    public function convertDelegatesToFrontmatterConverter(): void
    {
        $converter = new AntigravityFormatConverter();
        $mdc = "---\ndescription: Rule\nglobs: src/**/*.php\nalwaysApply: false\n---\n# Title\n\nBody.";

        $result = $converter->convert($mdc);

        self::assertSame("# Title\n\nBody.", $result);
    }
}
