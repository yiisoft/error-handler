<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Exception;

use Exception;

/**
 * `UserException` represents an exception that is meant to be shown to end users.
 *
 * @final
 */
class UserException extends Exception implements UserExceptionInterface
{
}
