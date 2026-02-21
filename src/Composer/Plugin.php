<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Symkit\BundleAiKit\Composer\Config\DefaultEditorConfigProvider;
use Symkit\BundleAiKit\Composer\Context\DefaultSyncContextResolver;
use Symkit\BundleAiKit\Composer\Contract\SyncContextResolverInterface;
use Symkit\BundleAiKit\Composer\Installer\AiRulesInstaller;

/**
 * Composer plugin: syncs AI rules/skills into project .cursor/, .claude/, .windsurf/.
 * Delegates context resolution (SRP) and sync (SRP) to injected abstractions (DIP).
 */
final class Plugin implements PluginInterface, Capable, EventSubscriberInterface
{
    public function __construct(
        ?SyncContextResolverInterface $contextResolver = null,
        ?AiRulesInstaller $installer = null,
    ) {
        $this->contextResolver = $contextResolver ?? DefaultSyncContextResolver::forBundleAiKit();
        $this->installer = $installer ?? new AiRulesInstaller(DefaultEditorConfigProvider::create());
    }

    private readonly SyncContextResolverInterface $contextResolver;
    private readonly AiRulesInstaller $installer;

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
            ScriptEvents::POST_INSTALL_CMD => 'syncAiFiles',
            ScriptEvents::POST_UPDATE_CMD => 'syncAiFiles',
        ];
    }

    public function syncAiFiles(Event $event): void
    {
        $context = $this->contextResolver->resolve($event);
        if (null === $context) {
            return;
        }

        $this->installer->sync($context);
    }
}
