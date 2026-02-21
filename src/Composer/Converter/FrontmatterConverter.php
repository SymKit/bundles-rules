<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Composer\Converter;

final readonly class FrontmatterConverter
{
    private const FRONTMATTER_PATTERN = '/\A---\r?\n(.*?)\r?\n---\r?\n/s';

    /**
     * @return array{frontmatter: array<string, string>, body: string}
     */
    public static function parseMdc(string $content): array
    {
        if (!preg_match(self::FRONTMATTER_PATTERN, $content, $matches)) {
            return ['frontmatter' => [], 'body' => $content];
        }

        $frontmatter = [];
        foreach (explode("\n", $matches[1]) as $line) {
            $line = trim($line);
            if ('' === $line || !str_contains($line, ':')) {
                continue;
            }
            $colonPos = (int) strpos($line, ':');
            $key = trim(substr($line, 0, $colonPos));
            $value = trim(substr($line, $colonPos + 1));
            if ('' !== $key) {
                $frontmatter[$key] = $value;
            }
        }

        $body = substr($content, \strlen($matches[0]));

        return ['frontmatter' => $frontmatter, 'body' => $body];
    }

    /**
     * Convert a Cursor .mdc file to Claude Code .md format.
     *
     * - alwaysApply: true or no globs -> no frontmatter (loads unconditionally)
     * - globs present -> ---\nglobs: <value>\n---
     * - description, name, alwaysApply -> stripped
     */
    public static function toClaudeFormat(string $mdcContent): string
    {
        $parsed = self::parseMdc($mdcContent);
        $fm = $parsed['frontmatter'];
        $body = $parsed['body'];

        $globs = $fm['globs'] ?? '';

        if ('' !== $globs && ($fm['alwaysApply'] ?? '') !== 'true') {
            return "---\nglobs: ".$globs."\n---\n".$body;
        }

        return $body;
    }

    /**
     * Convert a Cursor .mdc file to Windsurf .md format (strip all frontmatter).
     */
    public static function toWindsurfFormat(string $mdcContent): string
    {
        return self::parseMdc($mdcContent)['body'];
    }

    /**
     * Convert a Cursor .mdc file to Google Antigravity .md format (strip all frontmatter).
     */
    public static function toAntigravityFormat(string $mdcContent): string
    {
        return self::parseMdc($mdcContent)['body'];
    }
}
