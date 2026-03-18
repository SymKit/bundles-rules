<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Tests\Integration\Installer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symkit\BundleAiKit\Composer\Context\SkillNamesDiscoverer;
use Symkit\BundleAiKit\Composer\Context\SyncContext;
use Symkit\BundleAiKit\Composer\Installer\AiRulesInstaller;

final class AiRulesInstallerTest extends TestCase
{
    private string $tempDir;
    private string $packageDir;
    private string $projectDir;
    private AiRulesInstaller $installer;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().'/ai-rules-test-'.bin2hex(random_bytes(4));
        $this->packageDir = $this->tempDir.'/package';
        $this->projectDir = $this->tempDir.'/project';

        mkdir($this->packageDir.'/ai/cursor/rules', 0o755, true);
        mkdir($this->projectDir, 0o755, true);

        $this->installer = new AiRulesInstaller();
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    #[Test]
    public function syncCreatesRulesForCursor(): void
    {
        file_put_contents(
            $this->packageDir.'/ai/cursor/rules/test-rule.mdc',
            "---\ndescription: Test\nalwaysApply: true\n---\n# Test Rule\n\nContent.",
        );

        $context = new SyncContext($this->packageDir, $this->projectDir, []);
        $this->installer->sync($context);

        self::assertFileExists($this->projectDir.'/.cursor/rules/test-rule.mdc');
        self::assertStringEqualsFile(
            $this->projectDir.'/.cursor/rules/test-rule.mdc',
            "---\ndescription: Test\nalwaysApply: true\n---\n# Test Rule\n\nContent.",
        );
    }

    #[Test]
    public function syncCopiesNonMdcFilesInsideSkills(): void
    {
        mkdir($this->packageDir.'/ai/cursor/skills/feature', 0o755, true);
        file_put_contents(
            $this->packageDir.'/ai/cursor/skills/feature/helper.php',
            '<?php echo "hello";',
        );

        $context = new SyncContext($this->packageDir, $this->projectDir, ['feature']);
        $this->installer->sync($context);

        self::assertFileExists($this->projectDir.'/.cursor/skills/feature/helper.php');
        self::assertStringEqualsFile(
            $this->projectDir.'/.cursor/skills/feature/helper.php',
            '<?php echo "hello";',
        );
    }

    #[Test]
    public function syncCopiesEverySkillDirectoryLikeDiscovery(): void
    {
        mkdir($this->packageDir.'/ai/cursor/skills/alpha', 0o755, true);
        mkdir($this->packageDir.'/ai/cursor/skills/zeta', 0o755, true);
        file_put_contents($this->packageDir.'/ai/cursor/skills/alpha/SKILL.mdc', '# A');
        file_put_contents($this->packageDir.'/ai/cursor/skills/zeta/SKILL.mdc', '# Z');

        $skills = SkillNamesDiscoverer::discover($this->packageDir);
        self::assertSame(['alpha', 'zeta'], $skills);

        $context = new SyncContext($this->packageDir, $this->projectDir, $skills);
        $this->installer->sync($context);

        self::assertFileExists($this->projectDir.'/.cursor/skills/alpha/SKILL.mdc');
        self::assertFileExists($this->projectDir.'/.cursor/skills/zeta/SKILL.mdc');
    }

    #[Test]
    public function syncDoesNotDeleteExistingFiles(): void
    {
        $rulesDir = $this->projectDir.'/.cursor/rules';
        mkdir($rulesDir, 0o755, true);
        file_put_contents($rulesDir.'/my-custom-rule.mdc', '# My Custom Rule');

        file_put_contents(
            $this->packageDir.'/ai/cursor/rules/test-rule.mdc',
            '# Test Rule',
        );

        $context = new SyncContext($this->packageDir, $this->projectDir, []);
        $this->installer->sync($context);

        self::assertFileExists($rulesDir.'/my-custom-rule.mdc');
        self::assertStringEqualsFile($rulesDir.'/my-custom-rule.mdc', '# My Custom Rule');
        self::assertFileExists($rulesDir.'/test-rule.mdc');
    }

    #[Test]
    public function syncCopiesAgentsMdToProjectRoot(): void
    {
        file_put_contents($this->packageDir.'/ai/AGENTS.md', "# Bundle Kit\n\nHello.");

        $context = new SyncContext($this->packageDir, $this->projectDir, []);
        $this->installer->sync($context);

        self::assertFileExists($this->projectDir.'/AGENTS.md');
        self::assertStringEqualsFile($this->projectDir.'/AGENTS.md', "# Bundle Kit\n\nHello.");
    }

    #[Test]
    public function syncCopiesAgentFilesToCursorAgents(): void
    {
        mkdir($this->packageDir.'/ai/cursor/agents', 0o755, true);
        file_put_contents($this->packageDir.'/ai/cursor/agents/pm.md', "---\nname: pm\n---\n# PM");

        $context = new SyncContext($this->packageDir, $this->projectDir, []);
        $this->installer->sync($context);

        self::assertFileExists($this->projectDir.'/.cursor/agents/pm.md');
        self::assertStringEqualsFile($this->projectDir.'/.cursor/agents/pm.md', "---\nname: pm\n---\n# PM");
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
