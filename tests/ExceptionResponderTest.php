<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests;

use DomainException;
use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequestFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Di\Container;
use Yiisoft\ErrorHandler\Middleware\ExceptionResponder;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;
use Yiisoft\Injector\Injector;

final class ExceptionResponderTest extends TestCase
{
    public function testCode(): void
    {
        $middleware = $this->createMiddleware([
            DomainException::class => Status::BAD_REQUEST,
        ]);

        $this->assertSame(Status::BAD_REQUEST, $this->process($middleware)->getStatusCode());
    }

    public function testCallable(): void
    {
        $middleware = $this->createMiddleware([
            DomainException::class => function (ResponseFactoryInterface $responseFactory) {
                return $responseFactory->createResponse(Status::CREATED);
            },
        ]);

        $this->assertSame(Status::CREATED, $this->process($middleware)->getStatusCode());
    }

    public function testAnotherException(): void
    {
        $middleware = $this->createMiddleware([
            InvalidArgumentException::class => Status::BAD_REQUEST,
        ]);

        $this->expectException(DomainException::class);
        $this->process($middleware);
    }

    private function process(ExceptionResponder $middleware): ResponseInterface
    {
        return $middleware->process(
            (new ServerRequestFactory())->createServerRequest(Method::GET, 'http://example.com'),
            new class() implements RequestHandlerInterface {
                public function handle(ServerRequestInterface $request): ResponseInterface
                {
                    throw new DomainException();
                }
            }
        );
    }

    private function createMiddleware(array $exceptionMap): ExceptionResponder
    {
        return new ExceptionResponder(
            $exceptionMap,
            new ResponseFactory(),
            new Injector(
                new Container([
                    ResponseFactoryInterface::class => ResponseFactory::class,
                ]),
            ),
        );
    }
}
