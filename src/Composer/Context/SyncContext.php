<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Composer\Context;

/**
 * Value object: resolved context for running the AI rules sync.
 */
final readonly class SyncContext
{
    /**
     * @param list<string> $editors
     * @param list<string> $skills
     */
    public function __construct(
        public string $packagePath,
        public string $projectRoot,
        public array $editors,
        public array $skills,
    ) {
    }
}
