<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Exception;

use Throwable;

/**
 * Interface for exceptions that are meant to be shown to end users.
 * Such exceptions are often caused by mistakes of end users.
 */
interface UserExceptionInterface extends Throwable
{
}
