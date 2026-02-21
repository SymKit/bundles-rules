<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Composer\Installer;

use Symkit\BundleAiKit\Composer\Config\EditorConfig;
use Symkit\BundleAiKit\Composer\Context\SyncContext;
use Symkit\BundleAiKit\Composer\Contract\EditorConfigProviderInterface;

/**
 * Syncs AI rules and optional skills from package source to project dirs per editor.
 * Depends on EditorConfigProviderInterface for editor-specific paths and conversion (DIP).
 */
final class AiRulesInstaller
{
    private const SOURCE_RULES = '/ai/cursor/rules';
    private const SOURCE_SKILLS = '/ai/cursor/skills';

    public function __construct(
        private readonly EditorConfigProviderInterface $editorConfigProvider,
    ) {
    }

    public function sync(SyncContext $context): void
    {
        $rulesSource = $context->packagePath.self::SOURCE_RULES;
        $skillsSource = $context->packagePath.self::SOURCE_SKILLS;

        foreach ($context->editors as $editor) {
            $config = $this->editorConfigProvider->get($editor);
            if (null === $config) {
                continue;
            }

            $projectRulesDir = $context->projectRoot.'/'.$config->rulesDir;
            if (is_dir($rulesSource)) {
                $this->mergeDirectory($rulesSource, $projectRulesDir, $config);
            }

            foreach ($context->skills as $skillName) {
                if ('' === $skillName) {
                    continue;
                }
                $skillSource = $skillsSource.'/'.$skillName;
                $projectSkillDir = $context->projectRoot.'/'.$config->skillsDir.'/'.$skillName;
                if (is_dir($skillSource)) {
                    $this->mergeDirectory($skillSource, $projectSkillDir, $config);
                }
            }
        }
    }

    private function mergeDirectory(string $source, string $dest, EditorConfig $config): void
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

            $targetRelativePath = $relativePath;
            $isMdc = str_ends_with($relativePath, '.mdc');

            if ($isMdc && 'mdc' !== $config->fileExtension) {
                $targetRelativePath = substr($relativePath, 0, -4).'.'.$config->fileExtension;
            }

            $targetPath = $dest.'/'.$targetRelativePath;
            $targetDir = \dirname($targetPath);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0o755, true);
            }

            if ($isMdc && null !== $config->contentConverter) {
                $content = file_get_contents($item->getPathname());
                if (false === $content) {
                    continue;
                }
                file_put_contents($targetPath, $config->contentConverter->convert($content));
            } else {
                copy($item->getPathname(), $targetPath);
            }
        }
    }
}
