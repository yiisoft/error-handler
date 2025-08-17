<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Exception;

use Attribute;
use Exception;
use ReflectionClass;
use Throwable;

use function count;

/**
 * `UserException` is an exception anв class attribute that indicates
 * the exception message is safe to display to end users.
 *
 * Usage:
 * - throw directly (`throw new UserException(...)`) for explicit user-facing errors;
 * - annotate any exception class with the `#[UserException]` attribute
 *   to mark its messages as user-facing without extending this class.
 *
 * @final
 */
#[Attribute(Attribute::TARGET_CLASS)]
class UserException extends Exception
{
    public static function is(Throwable $throwable): bool
    {
        if ($throwable instanceof self) {
            return true;
        }

        $attributes = (new ReflectionClass($throwable))->getAttributes(self::class);
        return count($attributes) > 0;
    }
}
