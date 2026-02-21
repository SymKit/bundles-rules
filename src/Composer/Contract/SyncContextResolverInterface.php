<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Composer\Contract;

use Composer\Script\Event;
use Symkit\BundleAiKit\Composer\Context\SyncContext;

/**
 * Resolves package path, project root and kit config from a Composer script event.
 */
interface SyncContextResolverInterface
{
    public function resolve(Event $event): ?SyncContext;
}
