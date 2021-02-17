<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests;

use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequest;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Yiisoft\Di\Container;
use Yiisoft\ErrorHandler\Middleware\ErrorCatcher;
use Yiisoft\ErrorHandler\ErrorHandler;
use Yiisoft\ErrorHandler\Renderer\PlainTextRenderer;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;
use Yiisoft\Http\Header;

final class ErrorCatcherTest extends TestCase
{
    public function testAddedRenderer(): void
    {
        $mimeType = 'test/test';
        $catcher = $this->createErrorCatcher()->withRenderer($mimeType, PlainTextRenderer::class);
        $requestHandler = (new MockRequestHandler())->setHandleException(new RuntimeException());
        $response = $catcher->process($this->createServerRequest('GET', ['Accept' => [$mimeType]]), $requestHandler);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();
        $this->assertSame(PlainTextRenderer::DEFAULT_ERROR_MESSAGE, $content);
    }

    public function testThrownExceptionWithNotExistsRenderer()
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
        $this->createErrorCatcher()->withRenderer('test invalid mimeType', PlainTextRenderer::class);
    }

    public function testWithoutRenderers(): void
    {
        $catcher = $this->createErrorCatcher()->withoutRenderers();
        $requestHandler = (new MockRequestHandler())->setHandleException(new RuntimeException());
        $response = $catcher->process($this->createServerRequest('GET', ['Accept' => ['test/html']]), $requestHandler);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();
        $this->assertSame(PlainTextRenderer::DEFAULT_ERROR_MESSAGE, $content);
    }

    public function testWithoutRenderer(): void
    {
        $catcher = $this->createErrorCatcher()->withoutRenderers('*/*');
        $requestHandler = (new MockRequestHandler())->setHandleException(new RuntimeException());
        $response = $catcher->process($this->createServerRequest('GET', ['Accept' => ['test/html']]), $requestHandler);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();
        $this->assertSame(PlainTextRenderer::DEFAULT_ERROR_MESSAGE, $content);
    }

    public function testAdvancedAcceptHeader(): void
    {
        $contentType = 'text/html;version=2';
        $catcher = $this->createErrorCatcher()->withRenderer($contentType, PlainTextRenderer::class);
        $requestHandler = (new MockRequestHandler())->setHandleException(new RuntimeException());
        $response = $catcher->process(
            $this->createServerRequest('GET', ['Accept' => ['text/html', $contentType]]),
            $requestHandler
        );
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();
        $this->assertSame(PlainTextRenderer::DEFAULT_ERROR_MESSAGE, $content);
    }

    public function testDefaultContentType(): void
    {
        $catcher = $this->createErrorCatcher()->withRenderer('*/*', PlainTextRenderer::class);
        $requestHandler = (new MockRequestHandler())->setHandleException(new RuntimeException());
        $response = $catcher->process(
            $this->createServerRequest('GET', ['Accept' => ['test/test']]),
            $requestHandler
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
            (new MockRequestHandler())->setHandleException(new RuntimeException())
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

    private function createServerRequest(string $method, array $headers = []): ServerRequestInterface
    {
        return new ServerRequest([], [], [], [], [], $method, '/', $headers);
    }

    private function createErrorCatcher(): ErrorCatcher
    {
        return new ErrorCatcher(new ResponseFactory(), $this->createErrorHandler(), new Container());
    }
}
