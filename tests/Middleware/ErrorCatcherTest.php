<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Middleware;

use Psr\EventDispatcher\EventDispatcherInterface;
use HttpSoft\Message\Response;
use HttpSoft\Message\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Throwable;
use Yiisoft\ErrorHandler\Middleware\ErrorCatcher;
use Yiisoft\ErrorHandler\ThrowableResponseFactoryInterface;
use Yiisoft\Http\Status;

final class ErrorCatcherTest extends TestCase
{
    public function testSuccess(): void
    {
        $errorCatcher = new ErrorCatcher(
            $this->createThrowableResponseFactory(),
        );
        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };
        $response = $errorCatcher->process(
            new ServerRequest(),
            $handler,
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testError(): void
    {
        $errorCatcher = new ErrorCatcher(
            $this->createThrowableResponseFactory(),
        );
        $response = $errorCatcher->process(
            new ServerRequest(),
            $this->createRequestHandlerWithThrowable(),
        );

        $this->assertSame(Status::INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    public function testErrorWithEventDispatcher(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willThrowException(new RuntimeException('Event dispatcher error'));
        $errorCatcher = new ErrorCatcher(
            $this->createThrowableResponseFactory(),
            $eventDispatcher,
        );
        $response = $errorCatcher->process(
            new ServerRequest(),
            $this->createRequestHandlerWithThrowable(),
        );
        $this->assertSame(Status::INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    private function createThrowableResponseFactory(): ThrowableResponseFactoryInterface
    {
        return new class implements ThrowableResponseFactoryInterface {
            public function create(Throwable $throwable, ServerRequestInterface $request): ResponseInterface
            {
                return new Response(Status::INTERNAL_SERVER_ERROR);
            }
        };
    }

    private function createRequestHandlerWithThrowable(): RequestHandlerInterface
    {
        return new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new RuntimeException();
            }
        };
    }
}
