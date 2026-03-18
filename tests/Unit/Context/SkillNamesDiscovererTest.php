<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Tests\Unit\Context;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symkit\BundleAiKit\Composer\Context\SkillNamesDiscoverer;

final class SkillNamesDiscovererTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().'/skill-discover-'.bin2hex(random_bytes(4));
        mkdir($this->tempDir, 0o755, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    #[Test]
    public function returnsEmptyWhenSkillsDirMissing(): void
    {
        self::assertSame([], SkillNamesDiscoverer::discover($this->tempDir));
    }

    #[Test]
    public function returnsSortedDirectoryNamesOnly(): void
    {
        $skills = $this->tempDir.'/ai/cursor/skills';
        mkdir($skills.'/zebra', 0o755, true);
        mkdir($skills.'/alpha', 0o755, true);
        file_put_contents($skills.'/readme.txt', 'x');
        mkdir($skills.'/beta', 0o755, true);

        self::assertSame(['alpha', 'beta', 'zebra'], SkillNamesDiscoverer::discover($this->tempDir));
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($items as $item) {
            \assert($item instanceof \SplFileInfo);
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($dir);
    }
}
