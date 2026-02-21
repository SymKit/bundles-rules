<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Tests\Unit\Config;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symkit\BundleAiKit\Composer\Config\DefaultEditorConfigProvider;

final class DefaultEditorConfigProviderTest extends TestCase
{
    private DefaultEditorConfigProvider $provider;

    protected function setUp(): void
    {
        $this->provider = DefaultEditorConfigProvider::create();
    }

    #[Test]
    public function getCursorReturnsCorrectConfig(): void
    {
        $config = $this->provider->get('cursor');

        self::assertNotNull($config);
        self::assertSame('.cursor/rules', $config->rulesDir);
        self::assertSame('.cursor/skills', $config->skillsDir);
        self::assertSame('mdc', $config->fileExtension);
        self::assertNotNull($config->contentConverter);
    }

    #[Test]
    public function getClaudeReturnsCorrectConfig(): void
    {
        $config = $this->provider->get('claude');

        self::assertNotNull($config);
        self::assertSame('.claude/rules', $config->rulesDir);
        self::assertSame('.claude/rules/skills', $config->skillsDir);
        self::assertSame('md', $config->fileExtension);
        self::assertNotNull($config->contentConverter);
    }

    #[Test]
    public function getWindsurfReturnsCorrectConfig(): void
    {
        $config = $this->provider->get('windsurf');

        self::assertNotNull($config);
        self::assertSame('.windsurf/rules', $config->rulesDir);
        self::assertSame('.windsurf/rules/skills', $config->skillsDir);
        self::assertSame('md', $config->fileExtension);
        self::assertNotNull($config->contentConverter);
    }

    #[Test]
    public function getAntigravityReturnsCorrectConfig(): void
    {
        $config = $this->provider->get('antigravity');

        self::assertNotNull($config);
        self::assertSame('.agent/rules', $config->rulesDir);
        self::assertSame('.agent/rules/skills', $config->skillsDir);
        self::assertSame('md', $config->fileExtension);
        self::assertNotNull($config->contentConverter);
    }

    #[Test]
    public function getUnknownEditorReturnsNull(): void
    {
        self::assertNull($this->provider->get('unknown'));
    }
}
