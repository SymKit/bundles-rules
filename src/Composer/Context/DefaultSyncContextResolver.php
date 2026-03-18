<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Composer\Context;

use Composer\Script\Event;
use Symkit\BundleAiKit\Composer\Contract\SyncContextResolverInterface;

/**
 * Resolves sync context: Cursor only, all skills under ai/cursor/skills/.
 */
final readonly class DefaultSyncContextResolver implements SyncContextResolverInterface
{
    public function __construct(
        private string $packageName,
    ) {
    }

    public static function forBundleAiKit(): self
    {
        return new self('symkit/bundle-ai-kit');
    }

    public function resolve(Event $event): ?SyncContext
    {
        $composer = $event->getComposer();
        $installationManager = $composer->getInstallationManager();
        $localRepo = $composer->getRepositoryManager()->getLocalRepository();

        $package = null;
        foreach ($localRepo->getPackages() as $pkg) {
            if ($pkg->getName() === $this->packageName) {
                $package = $pkg;
                break;
            }
        }

        if (null === $package) {
            return null;
        }

        $packagePath = $installationManager->getInstallPath($package);
        if (!\is_string($packagePath) || !is_dir($packagePath)) {
            return null;
        }

        $vendorDir = $composer->getConfig()->get('vendor-dir');
        if (!\is_string($vendorDir)) {
            return null;
        }

        $projectRoot = realpath($vendorDir.'/..');
        if (false === $projectRoot) {
            return null;
        }

        $skills = SkillNamesDiscoverer::discover($packagePath);

        return new SyncContext($packagePath, $projectRoot, $skills);
    }
}
