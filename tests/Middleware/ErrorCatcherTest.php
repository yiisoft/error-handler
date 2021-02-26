<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Middleware;

use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequest;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Yiisoft\ErrorHandler\Middleware\ErrorCatcher;
use Yiisoft\ErrorHandler\ErrorHandler;
use Yiisoft\ErrorHandler\Renderer\HeaderRenderer;
use Yiisoft\ErrorHandler\Renderer\PlainTextRenderer;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;
use Yiisoft\Http\Header;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class ErrorCatcherTest extends TestCase
{
    public function testProcessWithHeadRequestMethod(): void
    {
        $response = $this->createErrorCatcher()->process(
            $this->createServerRequest('HEAD', ['Accept' => ['test/html']]),
            $this->createRequestHandlerWithThrowable(),
        );
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertEmpty($content);
        $this->assertSame([HeaderRenderer::DEFAULT_ERROR_MESSAGE], $response->getHeader('X-Error-Message'));
    }

    public function testProcessWithFailAcceptRequestHeader(): void
    {
        $response = $this->createErrorCatcher()->process(
            $this->createServerRequest('GET', ['Accept' => ['text/plain;q=2.0']]),
            $this->createRequestHandlerWithThrowable(),
        );
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertNotSame(PlainTextRenderer::DEFAULT_ERROR_MESSAGE, $content);
        $this->assertStringContainsString('<html', $content);
    }

    public function testAddedRenderer(): void
    {
        $mimeType = 'test/test';
        $catcher = $this->createErrorCatcher()->withRenderer($mimeType, PlainTextRenderer::class);
        $response = $catcher->process(
            $this->createServerRequest('GET', ['Accept' => [$mimeType]]),
            $this->createRequestHandlerWithThrowable(),
        );
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertSame(PlainTextRenderer::DEFAULT_ERROR_MESSAGE, $content);
    }

    public function testThrownExceptionWithRendererIsNotImplementThrowableRendererInterface()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage(
            'Class "' . self::class . '" does not implement "' . ThrowableRendererInterface::class . '".',
        );
        $this->createErrorCatcher()->withRenderer('test/test', self::class);
    }

    public function testThrownExceptionWithInvalidContentType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Invalid content type.');
        $this->createErrorCatcher()->withRenderer('test invalid content type', PlainTextRenderer::class);
    }

    public function testWithoutRenderers(): void
    {
        $catcher = $this->createErrorCatcher()->withoutRenderers();
        $response = $catcher->process(
            $this->createServerRequest('GET', ['Accept' => ['test/html']]),
            $this->createRequestHandlerWithThrowable(),
        );
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertSame(PlainTextRenderer::DEFAULT_ERROR_MESSAGE, $content);
    }

    public function testWithoutRenderer(): void
    {
        $catcher = $this->createErrorCatcher()->withoutRenderers('*/*');
        $response = $catcher->process(
            $this->createServerRequest('GET', ['Accept' => ['test/html']]),
            $this->createRequestHandlerWithThrowable(),
        );
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertSame(PlainTextRenderer::DEFAULT_ERROR_MESSAGE, $content);
    }

    public function testAdvancedAcceptHeader(): void
    {
        $contentType = 'text/html;version=2';
        $catcher = $this->createErrorCatcher()->withRenderer($contentType, PlainTextRenderer::class);
        $response = $catcher->process(
            $this->createServerRequest('GET', ['Accept' => ['text/html', $contentType]]),
            $this->createRequestHandlerWithThrowable(),
        );
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertSame(PlainTextRenderer::DEFAULT_ERROR_MESSAGE, $content);
    }

    public function testDefaultContentType(): void
    {
        $catcher = $this->createErrorCatcher()->withRenderer('*/*', PlainTextRenderer::class);
        $response = $catcher->process(
            $this->createServerRequest('GET', ['Accept' => ['test/test']]),
            $this->createRequestHandlerWithThrowable(),
        );
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertSame(PlainTextRenderer::DEFAULT_ERROR_MESSAGE, $content);
    }

    public function testForceContentType(): void
    {
        $catcher = $this->createErrorCatcher()->forceContentType('application/json');
        $response = $catcher->process(
            $this->createServerRequest('GET', ['Accept' => ['text/xml']]),
            $this->createRequestHandlerWithThrowable(),
        );
        $response->getBody()->rewind();

        $this->assertSame('application/json', $response->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testForceContentTypeSetInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('The renderer for image/gif is not set.');
        $this->createErrorCatcher()->forceContentType('image/gif');
    }

    private function createErrorHandler(): ErrorHandler
    {
        $logger = $this->createMock(LoggerInterface::class);
        return new ErrorHandler($logger, new PlainTextRenderer());
    }

    private function createErrorCatcher(): ErrorCatcher
    {
        $container = new SimpleContainer([], fn (string $className): object => new $className());
        return new ErrorCatcher(new ResponseFactory(), $this->createErrorHandler(), $container);
    }

    private function createServerRequest(string $method, array $headers = []): ServerRequestInterface
    {
        return new ServerRequest([], [], [], [], [], $method, '/', $headers);
    }

    private function createRequestHandlerWithThrowable(): RequestHandlerInterface
    {
        return new class() implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new RuntimeException();
            }
        };
    }
}
