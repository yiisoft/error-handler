<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler;

use Throwable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ThrowableHandlerInterface
{
    /**
     * Handles a Throwable and produces a response.
     */
    public function handle(Throwable $t, ServerRequestInterface $request): ResponseInterface;
}
