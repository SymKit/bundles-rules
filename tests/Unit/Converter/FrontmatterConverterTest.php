<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Tests\Unit\Converter;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symkit\BundleAiKit\Composer\Converter\FrontmatterConverter;

final class FrontmatterConverterTest extends TestCase
{
    #[Test]
    public function parseMdcWithFrontmatter(): void
    {
        $content = "---\ndescription: A rule\nglobs: src/**/*.php\nalwaysApply: false\n---\n# Title\n\nBody content.";

        $result = FrontmatterConverter::parseMdc($content);

        self::assertSame('A rule', $result['frontmatter']['description']);
        self::assertSame('src/**/*.php', $result['frontmatter']['globs']);
        self::assertSame('false', $result['frontmatter']['alwaysApply']);
        self::assertSame("# Title\n\nBody content.", $result['body']);
    }

    #[Test]
    public function parseMdcWithoutFrontmatter(): void
    {
        $content = "# Title\n\nNo frontmatter here.";

        $result = FrontmatterConverter::parseMdc($content);

        self::assertSame([], $result['frontmatter']);
        self::assertSame($content, $result['body']);
    }

    #[Test]
    public function parseMdcWithEmptyFrontmatter(): void
    {
        $content = "---\n\n---\n# Title";

        $result = FrontmatterConverter::parseMdc($content);

        self::assertSame([], $result['frontmatter']);
        self::assertSame('# Title', $result['body']);
    }

    #[Test]
    public function toClaudeFormatWithAlwaysApplyTrue(): void
    {
        $mdc = "---\ndescription: Main rule\nalwaysApply: true\n---\n# Main\n\nContent.";

        $result = FrontmatterConverter::toClaudeFormat($mdc);

        self::assertSame("# Main\n\nContent.", $result);
    }

    #[Test]
    public function toClaudeFormatWithGlobs(): void
    {
        $mdc = "---\ndescription: PHP rule\nglobs: src/**/*.php\nalwaysApply: false\n---\n# PHP\n\nContent.";

        $result = FrontmatterConverter::toClaudeFormat($mdc);

        self::assertSame("---\nglobs: src/**/*.php\n---\n# PHP\n\nContent.", $result);
    }

    #[Test]
    public function toClaudeFormatWithGlobsAndAlwaysApplyTrue(): void
    {
        $mdc = "---\ndescription: Rule\nglobs: src/**/*.php\nalwaysApply: true\n---\n# Title\n\nBody.";

        $result = FrontmatterConverter::toClaudeFormat($mdc);

        self::assertSame("# Title\n\nBody.", $result);
    }

    #[Test]
    public function toClaudeFormatWithoutFrontmatter(): void
    {
        $content = "# Skill\n\nSkill content.";

        $result = FrontmatterConverter::toClaudeFormat($content);

        self::assertSame($content, $result);
    }

    #[Test]
    public function toClaudeFormatWithNoGlobs(): void
    {
        $mdc = "---\ndescription: A rule\nalwaysApply: false\n---\n# Title\n\nBody.";

        $result = FrontmatterConverter::toClaudeFormat($mdc);

        self::assertSame("# Title\n\nBody.", $result);
    }

    #[Test]
    public function toWindsurfFormatStripsAllFrontmatter(): void
    {
        $mdc = "---\ndescription: Rule\nglobs: src/**/*.php\nalwaysApply: false\n---\n# Title\n\nBody.";

        $result = FrontmatterConverter::toWindsurfFormat($mdc);

        self::assertSame("# Title\n\nBody.", $result);
    }

    #[Test]
    public function toWindsurfFormatWithoutFrontmatter(): void
    {
        $content = "# Title\n\nBody.";

        $result = FrontmatterConverter::toWindsurfFormat($content);

        self::assertSame($content, $result);
    }

    #[Test]
    public function toAntigravityFormatStripsAllFrontmatter(): void
    {
        $mdc = "---\ndescription: Rule\nglobs: src/**/*.php\nalwaysApply: false\n---\n# Title\n\nBody.";

        $result = FrontmatterConverter::toAntigravityFormat($mdc);

        self::assertSame("# Title\n\nBody.", $result);
    }

    #[Test]
    public function toAntigravityFormatWithoutFrontmatter(): void
    {
        $content = "# Title\n\nBody.";

        $result = FrontmatterConverter::toAntigravityFormat($content);

        self::assertSame($content, $result);
    }
}
