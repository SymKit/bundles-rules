<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Composer\Installer;

use Symkit\BundleAiKit\Composer\Context\SyncContext;

/**
 * Syncs Cursor rules (.mdc), all skills, AGENTS.md, and .cursor/agents from the package.
 */
final class AiRulesInstaller
{
    private const SOURCE_RULES = '/ai/cursor/rules';
    private const SOURCE_SKILLS = '/ai/cursor/skills';
    private const SOURCE_AGENTS = '/ai/cursor/agents';
    private const SOURCE_AGENTS_MD = '/ai/AGENTS.md';

    private const DEST_RULES = '.cursor/rules';
    private const DEST_SKILLS = '.cursor/skills';
    private const DEST_AGENTS = '.cursor/agents';

    public function sync(SyncContext $context): void
    {
        $rulesSource = $context->packagePath.self::SOURCE_RULES;
        $skillsSource = $context->packagePath.self::SOURCE_SKILLS;
        $agentsSource = $context->packagePath.self::SOURCE_AGENTS;
        $agentsMdSource = $context->packagePath.self::SOURCE_AGENTS_MD;
        $root = $context->projectRoot;

        $this->syncAgentsMd($agentsMdSource, $root.'/AGENTS.md');

        $projectRulesDir = $root.'/'.self::DEST_RULES;
        if (is_dir($rulesSource)) {
            $this->mergeDirectory($rulesSource, $projectRulesDir);
        }

        foreach ($context->skills as $skillName) {
            if ('' === $skillName) {
                continue;
            }
            $skillSource = $skillsSource.'/'.$skillName;
            $projectSkillDir = $root.'/'.self::DEST_SKILLS.'/'.$skillName;
            if (is_dir($skillSource)) {
                $this->mergeDirectory($skillSource, $projectSkillDir);
            }
        }

        if (is_dir($agentsSource)) {
            $this->mergeDirectory($agentsSource, $root.'/'.self::DEST_AGENTS);
        }
    }

    private function syncAgentsMd(string $source, string $dest): void
    {
        if (!is_file($source)) {
            return;
        }

        if (!copy($source, $dest)) {
            return;
        }
    }

    private function mergeDirectory(string $source, string $dest): void
    {
        if (!is_dir($source)) {
            return;
        }

        if (!is_dir($dest)) {
            mkdir($dest, 0o755, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST,
        );

        foreach ($iterator as $item) {
            /** @var \SplFileInfo $item */
            $relativePath = substr($item->getPathname(), \strlen($source) + 1);

            if ($item->isDir()) {
                $targetDir = $dest.'/'.$relativePath;
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0o755, true);
                }
                continue;
            }

            $targetPath = $dest.'/'.$relativePath;
            $targetDir = \dirname($targetPath);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0o755, true);
            }

            copy($item->getPathname(), $targetPath);
        }
    }
}
