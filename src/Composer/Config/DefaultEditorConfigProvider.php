<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Composer\Config;

use Symkit\BundleAiKit\Composer\Contract\ContentConverterInterface;
use Symkit\BundleAiKit\Composer\Contract\EditorConfigProviderInterface;
use Symkit\BundleAiKit\Composer\Converter\AntigravityFormatConverter;
use Symkit\BundleAiKit\Composer\Converter\ClaudeFormatConverter;
use Symkit\BundleAiKit\Composer\Converter\IdentityConverter;
use Symkit\BundleAiKit\Composer\Converter\WindsurfFormatConverter;

/**
 * Provides config for supported editors: cursor, claude, windsurf, antigravity.
 */
final class DefaultEditorConfigProvider implements EditorConfigProviderInterface
{
    public function __construct(
        private readonly ContentConverterInterface $cursorConverter,
        private readonly ContentConverterInterface $claudeConverter,
        private readonly ContentConverterInterface $windsurfConverter,
        private readonly ContentConverterInterface $antigravityConverter,
    ) {
    }

    public static function create(): self
    {
        return new self(
            new IdentityConverter(),
            new ClaudeFormatConverter(),
            new WindsurfFormatConverter(),
            new AntigravityFormatConverter(),
        );
    }

    public function get(string $editor): ?EditorConfig
    {
        return match ($editor) {
            'cursor' => new EditorConfig(
                '.cursor/rules',
                '.cursor/skills',
                'mdc',
                $this->cursorConverter,
            ),
            'claude' => new EditorConfig(
                '.claude/rules',
                '.claude/rules/skills',
                'md',
                $this->claudeConverter,
            ),
            'windsurf' => new EditorConfig(
                '.windsurf/rules',
                '.windsurf/rules/skills',
                'md',
                $this->windsurfConverter,
            ),
            'antigravity' => new EditorConfig(
                '.agent/rules',
                '.agent/rules/skills',
                'md',
                $this->antigravityConverter,
            ),
            default => null,
        };
    }
}
