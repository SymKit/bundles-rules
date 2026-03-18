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
        $context = new SyncContext('/pkg', '/root', ['a', 'b']);

        self::assertSame('/pkg', $context->packagePath);
        self::assertSame('/root', $context->projectRoot);
        self::assertSame(['a', 'b'], $context->skills);
    }

    #[Test]
    public function emptySkills(): void
    {
        $context = new SyncContext('/pkg', '/root', []);

        self::assertSame([], $context->skills);
    }
}
