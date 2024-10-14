<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler;

use Throwable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * `ThrowableResponseFactoryInterface` produces a response for `Throwable` object.
 */
interface ThrowableResponseFactoryInterface
{
    /**
     * Handles a `Throwable` object and produces a response.
     */
    public function create(Throwable $throwable, ServerRequestInterface $request): ResponseInterface;
}
