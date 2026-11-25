<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Support;

use RuntimeException;

function loadFileLevelClosureException(): RuntimeException
{
    /** @var RuntimeException $exception */
    $exception = require __DIR__ . '/file_level_closure_exception.php';

    return $exception;
}
