<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Support;

use RuntimeException;

/**
 * Unsafe HTML <img src="x" onerror="alert(1)"> should not survive.
 *
 * {@link javascript:alert(1) Click me} and {@link https://www.yiiframework.com Safe link}.
 */
final class TestUnsafeDocBlockException extends RuntimeException {}
