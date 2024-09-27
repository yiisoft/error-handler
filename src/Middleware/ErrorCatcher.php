<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Middleware;

use Throwable;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\ErrorHandler\CompositeException;
use Yiisoft\ErrorHandler\Event\ApplicationError;
use Yiisoft\ErrorHandler\ThrowableResponseFactoryInterface;

/**
 * `ErrorCatcher` catches all throwables from the next middlewares
 * and renders it with a handler that implements the `ThrowableResponseFactoryInterface`.
 */
final class ErrorCatcher implements MiddlewareInterface
{
    public function __construct(
        private ThrowableResponseFactoryInterface $throwableResponseFactory,
        private ?EventDispatcherInterface $eventDispatcher = null,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $t) {
            try {
                $this->eventDispatcher?->dispatch(new ApplicationError($t));
            } catch (Throwable $e) {
                $t = new CompositeException($e, $t);
            }

            return $this->throwableResponseFactory->create($t, $request);
        }
    }
}
