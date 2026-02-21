<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Composer\Context;

use Composer\Script\Event;
use Symkit\BundleAiKit\Composer\Contract\SyncContextResolverInterface;

/**
 * Resolves sync context from Composer script event using a fixed package name and config key.
 */
final readonly class DefaultSyncContextResolver implements SyncContextResolverInterface
{
    public function __construct(
        private string $packageName,
        private string $configKey,
    ) {
    }

    public static function forBundleAiKit(): self
    {
        return new self('symkit/bundle-ai-kit', 'bundle-ai-kit');
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

        /** @var array<string, mixed> $extra */
        $extra = $composer->getPackage()->getExtra()[$this->configKey] ?? [];
        $rawEditors = \is_array($extra['editors'] ?? null) ? $extra['editors'] : ['cursor'];
        $rawSkills = \is_array($extra['skills'] ?? null) ? $extra['skills'] : [];

        /** @var list<string> $editors */
        $editors = array_values(array_filter($rawEditors, '\is_string'));
        /** @var list<string> $skills */
        $skills = array_values(array_filter($rawSkills, '\is_string'));

        return new SyncContext($packagePath, $projectRoot, $editors, $skills);
    }
}
