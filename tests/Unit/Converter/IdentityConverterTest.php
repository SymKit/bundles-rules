<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Tests\Unit\Converter;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symkit\BundleAiKit\Composer\Converter\IdentityConverter;

final class IdentityConverterTest extends TestCase
{
    #[Test]
    public function convertReturnsContentUnchanged(): void
    {
        $converter = new IdentityConverter();
        $content = "---\ndescription: Rule\n---\n# Title\n\nBody.";

        self::assertSame($content, $converter->convert($content));
    }
}
