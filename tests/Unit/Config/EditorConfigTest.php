<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Tests\Unit\Config;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symkit\BundleAiKit\Composer\Config\EditorConfig;
use Symkit\BundleAiKit\Composer\Converter\IdentityConverter;

final class EditorConfigTest extends TestCase
{
    #[Test]
    public function constructionAndPropertyAccess(): void
    {
        $converter = new IdentityConverter();
        $config = new EditorConfig('.cursor/rules', '.cursor/skills', 'mdc', $converter);

        self::assertSame('.cursor/rules', $config->rulesDir);
        self::assertSame('.cursor/skills', $config->skillsDir);
        self::assertSame('mdc', $config->fileExtension);
        self::assertSame($converter, $config->contentConverter);
    }

    #[Test]
    public function constructionWithNullConverter(): void
    {
        $config = new EditorConfig('.windsurf/rules', '.windsurf/skills', 'md', null);

        self::assertNull($config->contentConverter);
    }
}
