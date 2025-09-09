<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler;

use Throwable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * `ThrowableResponseActionInterface` produces a response for `Throwable` object.
 */
interface ThrowableResponseActionInterface
{
    /**
     * Handles a `Throwable` object and produces a response.
     */
    public function handle(ServerRequestInterface $request, Throwable $throwable): ResponseInterface;
}
