<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Composer\Context;

/**
 * Lists skill folder names under ai/cursor/skills/ (sorted).
 */
final class SkillNamesDiscoverer
{
    /**
     * @return list<string>
     */
    public static function discover(string $packagePath): array
    {
        $dir = $packagePath.'/ai/cursor/skills';
        if (!is_dir($dir)) {
            return [];
        }

        $names = [];
        foreach (scandir($dir) as $entry) {
            if ('.' === $entry || '..' === $entry) {
                continue;
            }
            if (is_dir($dir.'/'.$entry)) {
                $names[] = $entry;
            }
        }
        sort($names);

        return $names;
    }
}
