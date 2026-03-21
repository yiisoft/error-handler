<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Support;

use RuntimeException;

/**
 * Safe markdown link with query parameters [Yii Search](https://www.yiiframework.com/search?q=error&lang=en).
 */
final class TestQueryStringDocBlockException extends RuntimeException {}
