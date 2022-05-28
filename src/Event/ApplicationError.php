<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Event;

use Throwable;

/**
 * ApplicationError represents an application error event.
 */
final class ApplicationError
{
    private Throwable $throwable;

    public function __construct(Throwable $throwable)
    {
        $this->throwable = $throwable;
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}
