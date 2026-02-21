<?php

declare(strict_types=1);

namespace Seb\SymfonyBundleCursorKit\Composer;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class CursorRulesInstaller
{
    /**
     * Sync package rules (always) and optionally selected skills to project .cursor/.
     * Only copies/overwrites files that exist in the package; never deletes user files.
     *
     * @param list<string> $skills Skill directory names to install (e.g. ['symfony-bundle-core', 'symfony-bundle-ux'])
     */
    public static function sync(string $packagePath, string $projectRoot, array $skills = []): void
    {
        $rulesSource = $packagePath . '/.cursor/rules';
        $projectRulesDir = $projectRoot . '/.cursor/rules';

        if (is_dir($rulesSource)) {
            self::mergeDirectory($rulesSource, $projectRulesDir);
        }

        $skillsSourceDir = $packagePath . '/skills';
        $projectSkillsDir = $projectRoot . '/.cursor/skills';

        foreach ($skills as $skillName) {
            if ($skillName === '' || !is_string($skillName)) {
                continue;
            }
            $skillSource = $skillsSourceDir . '/' . $skillName;
            if (is_dir($skillSource)) {
                self::mergeDirectory($skillSource, $projectSkillsDir . '/' . $skillName);
            }
        }
    }

    /**
     * Copy files from source to dest, overwriting only files that exist in source.
     * Creates destination directory and subdirectories as needed. Never deletes anything in dest.
     */
    private static function mergeDirectory(string $source, string $dest): void
    {
        if (!is_dir($source)) {
            return;
        }

        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            /** @var SplFileInfo $item */
            $relativePath = substr($item->getPathname(), strlen($source) + 1);
            $targetPath = $dest . '/' . $relativePath;

            if ($item->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                $targetDir = dirname($targetPath);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                copy($item->getPathname(), $targetPath);
            }
        }
    }
}
