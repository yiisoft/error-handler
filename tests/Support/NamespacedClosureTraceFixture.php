<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Support;

use RuntimeException;

final class NamespacedClosureTraceFixture
{
    public static function createException(): RuntimeException
    {
        $closure = function (): void {
            throw new RuntimeException('test');
        };

        try {
            $closure();
        } catch (RuntimeException $e) {
            return $e;
        }

        throw new RuntimeException('Namespaced closure did not throw RuntimeException.');
    }
}
