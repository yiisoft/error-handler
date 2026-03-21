<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Support;

use RuntimeException;

/**
 * Description with raw `inline-code` and {@see RuntimeException}.
 */
final class TestInlineCodeDocBlockException extends RuntimeException {}
