<?php

declare(strict_types=1);

namespace Seb\SymfonyBundleCursorKit\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

final class Plugin implements PluginInterface, Capable, EventSubscriberInterface
{
    private const PACKAGE_NAME = 'seb/symfony-bundle-cursor-kit';

    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    public function getCapabilities(): array
    {
        return [
            EventSubscriberInterface::class => self::class,
        ];
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'syncCursorFiles',
            ScriptEvents::POST_UPDATE_CMD => 'syncCursorFiles',
        ];
    }

    public function syncCursorFiles(Event $event): void
    {
        $composer = $event->getComposer();
        $installationManager = $composer->getInstallationManager();
        $localRepo = $composer->getRepositoryManager()->getLocalRepository();

        $package = null;
        foreach ($localRepo->getPackages() as $pkg) {
            if ($pkg->getName() === self::PACKAGE_NAME) {
                $package = $pkg;
                break;
            }
        }

        if ($package === null) {
            return;
        }

        $packagePath = $installationManager->getInstallPath($package);
        if (!is_string($packagePath) || !is_dir($packagePath)) {
            return;
        }

        $vendorDir = $composer->getConfig()->get('vendor-dir');
        $projectRoot = realpath($vendorDir . '/..');
        if ($projectRoot === false) {
            return;
        }

        $rootPackage = $composer->getPackage();
        $extra = $rootPackage->getExtra()['symfony-bundle-cursor-kit'] ?? [];
        $skills = is_array($extra['skills'] ?? null) ? $extra['skills'] : [];

        CursorRulesInstaller::sync($packagePath, $projectRoot, $skills);
    }
}
