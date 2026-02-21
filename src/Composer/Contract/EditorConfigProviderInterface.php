<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Composer\Contract;

use Symkit\BundleAiKit\Composer\Config\EditorConfig;

/**
 * Provides editor-specific config (paths and content converter) for AI rules sync.
 */
interface EditorConfigProviderInterface
{
    public function get(string $editor): ?EditorConfig;
}
