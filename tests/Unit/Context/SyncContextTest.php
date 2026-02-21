<?php

declare(strict_types=1);

namespace Symkit\BundleAiKit\Tests\Unit\Context;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symkit\BundleAiKit\Composer\Context\SyncContext;

final class SyncContextTest extends TestCase
{
    #[Test]
    public function constructionAndPropertyAccess(): void
    {
        $context = new SyncContext(
            '/path/to/package',
            '/path/to/project',
            ['cursor', 'claude'],
            ['symfony-bundle-core'],
        );

        self::assertSame('/path/to/package', $context->packagePath);
        self::assertSame('/path/to/project', $context->projectRoot);
        self::assertSame(['cursor', 'claude'], $context->editors);
        self::assertSame(['symfony-bundle-core'], $context->skills);
    }

    #[Test]
    public function constructionWithEmptyArrays(): void
    {
        $context = new SyncContext('/pkg', '/root', [], []);

        self::assertSame([], $context->editors);
        self::assertSame([], $context->skills);
    }
}
