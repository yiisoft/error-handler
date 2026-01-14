<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Middleware;

use DomainException;
use HttpSoft\Message\Response;
use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ResponseTrait;
use HttpSoft\Message\ServerRequest;
use HttpSoft\Message\ServerRequestFactory;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\ErrorHandler\Middleware\ExceptionResponder;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;
use Yiisoft\Injector\Injector;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class ExceptionResponderTest extends TestCase
{
    public function testCode(): void
    {
        $middleware = $this->createMiddleware([
            DomainException::class => Status::BAD_REQUEST,
        ]);

        $this->assertSame(
            Status::BAD_REQUEST,
            $this
                ->process($middleware)
                ->getStatusCode(),
        );
    }

    public function testCallable(): void
    {
        $request = new ServerRequest(headers: ['X-TEST' => ['HELLO']]);
        $middleware = $this->createMiddleware([
            DomainException::class
                => static function (ResponseFactoryInterface $responseFactory, ServerRequestInterface $request) {
                    return $responseFactory->createResponse(Status::CREATED, $request->getHeaderLine('X-TEST'));
                },
        ]);

        $response = $middleware->process(
            $request,
            new class implements RequestHandlerInterface {
                public function handle(ServerRequestInterface $request): ResponseInterface
                {
                    throw new DomainException();
                }
            },
        );

        $this->assertSame(Status::CREATED, $response->getStatusCode());
        $this->assertSame('HELLO', $response->getReasonPhrase());
    }

    public function testAnotherException(): void
    {
        $middleware = $this->createMiddleware([
            InvalidArgumentException::class => Status::BAD_REQUEST,
        ]);

        $this->expectException(DomainException::class);
        $this->process($middleware);
    }

    public function testCheckResponseBody(): void
    {
        $middleware = $this->createMiddleware(checkResponseBody: true);
        $request = (new ServerRequestFactory())->createServerRequest(Method::GET, 'http://example.com');
        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new class implements ResponseInterface {
                    use ResponseTrait;

                    public function getBody(): StreamInterface
                    {
                        throw new LogicException('test');
                    }
                };
            }
        };

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('test');
        $middleware->process($request, $handler);
    }

    public function testSuccess(): void
    {
        $middleware = $this->createMiddleware(checkResponseBody: true);
        $request = (new ServerRequestFactory())->createServerRequest(Method::GET, 'http://example.com');
        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        $response = $middleware->process($request, $handler);

        $this->assertSame(200, $response->getStatusCode());
    }

    private function process(ExceptionResponder $middleware): ResponseInterface
    {
        return $middleware->process(
            (new ServerRequestFactory())->createServerRequest(Method::GET, 'http://example.com'),
            new class implements RequestHandlerInterface {
                public function handle(ServerRequestInterface $request): ResponseInterface
                {
                    throw new DomainException();
                }
            },
        );
    }

    private function createMiddleware(
        array $exceptionMap = [],
        bool $checkResponseBody = false,
    ): ExceptionResponder {
        return new ExceptionResponder(
            $exceptionMap,
            new ResponseFactory(),
            new Injector(
                new SimpleContainer([
                    ResponseFactoryInterface::class => new ResponseFactory(),
                ]),
            ),
            $checkResponseBody,
        );
    }
}
