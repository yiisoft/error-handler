<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler;

use Exception;
use Throwable;

/**
 * Aggregate multiple exceptions into one.
 */
final class CompositeException extends Exception
{
    /**
     * @var Throwable[]
     */
    public array $rest;

    public function __construct(
        private \Throwable $first,
        \Throwable ...$rest,
    ) {
        $this->rest = $rest;
        parent::__construct($first->getMessage(), $first->getCode(), $first);
    }

    public function getFirstException(): Throwable
    {
        return $this->first;
    }

    public function getPreviousExceptions(): array
    {
        return $this->rest;
    }
}
