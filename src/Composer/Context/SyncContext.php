<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Composer\Context;

/**
 * Value object: package path, project root, skill names to sync.
 */
final readonly class SyncContext
{
    /**
     * @param list<string> $skills
     */
    public function __construct(
        public string $packagePath,
        public string $projectRoot,
        public array $skills,
    ) {
    }
}
