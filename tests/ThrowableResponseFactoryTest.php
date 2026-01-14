<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests;

use HttpSoft\Message\ResponseFactory;
use LogicException;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Yiisoft\ErrorHandler\ErrorHandler;
use Yiisoft\ErrorHandler\HeadersProvider;
use Yiisoft\ErrorHandler\Renderer\PlainTextRenderer;
use Yiisoft\ErrorHandler\RendererProvider\ContentTypeRendererProvider;
use Yiisoft\ErrorHandler\Tests\Support\TestHelper;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;
use Yiisoft\ErrorHandler\ThrowableResponseFactory;
use Yiisoft\Test\Support\Container\SimpleContainer;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

final class ThrowableResponseFactoryTest extends TestCase
{
    public function testBase(): void
    {
        $factory = new ThrowableResponseFactory(
            new ResponseFactory(),
            new ErrorHandler(
                new NullLogger(),
                new PlainTextRenderer(),
            ),
            new ContentTypeRendererProvider(
                new SimpleContainer(),
            ),
        );

        $response = $factory->create(
            new LogicException('test message'),
            TestHelper::createRequest(),
        );

        assertSame(500, $response->getStatusCode());
        assertSame(ThrowableRendererInterface::DEFAULT_ERROR_MESSAGE, TestHelper::getResponseContent($response));
    }

    public function testHeaders(): void
    {
        $factory = new ThrowableResponseFactory(
            new ResponseFactory(),
            new ErrorHandler(
                new NullLogger(),
                new PlainTextRenderer(),
            ),
            new ContentTypeRendererProvider(
                new SimpleContainer(),
            ),
            new HeadersProvider(['X-Test' => ['on'], 'X-Test-Custom' => 'hello']),
        );

        $response = $factory->create(
            new LogicException('test message'),
            TestHelper::createRequest(),
        );

        assertTrue($response->hasHeader('X-Test'));
        assertSame('on', $response->getHeaderLine('X-Test'));
        assertTrue($response->hasHeader('X-Test-Custom'));
        assertSame('hello', $response->getHeaderLine('X-Test-Custom'));
    }
}
