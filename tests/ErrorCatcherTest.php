<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests;

use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Di\Container;
use Yiisoft\ErrorHandler\Middleware\ErrorCatcher;
use Yiisoft\ErrorHandler\ErrorHandler;
use Yiisoft\Http\Header;

final class ErrorCatcherTest extends TestCase
{
    private const DEFAULT_RENDERER_RESPONSE = 'default-renderer-test';

    public function testAddedRenderer(): void
    {
        $expectedRendererOutput = 'expectedRendererOutput';
        $containerId = 'testRenderer';
        $container = $this->createContainerWithThrowableRenderer($containerId, $expectedRendererOutput);
        $mimeType = 'test/test';
        $catcher = $this->createErrorCatcher($container)->withRenderer($mimeType, $containerId);
        $requestHandler = (new MockRequestHandler())->setHandleException(new \RuntimeException());
        $response = $catcher->process($this->createServerRequest('GET', ['Accept' => [$mimeType]]), $requestHandler);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();
        $this->assertNotSame(self::DEFAULT_RENDERER_RESPONSE, $content);
        $this->assertSame($expectedRendererOutput, $content);
    }

    public function testThrownExceptionWithNotExistsRenderer()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectErrorMessage('The renderer "InvalidRendererClass" cannot be found.');

        $this->createErrorCatcher(new Container())->withRenderer('test/test', \InvalidRendererClass::class);
    }

    public function testThrownExceptionWithInvalidMimeType()
    {
        $containerId = 'testRenderer';
        $container = $this->createContainerWithThrowableRenderer($containerId, '');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectErrorMessage('Invalid mime type.');

        $this->createErrorCatcher($container)->withRenderer('test invalid mimeType', $containerId);
    }

    public function testWithoutRenderers(): void
    {
        $container = new Container();
        $catcher = $this->createErrorCatcher($container)->withoutRenderers();
        $requestHandler = (new MockRequestHandler())->setHandleException(new \RuntimeException());
        $response = $catcher->process($this->createServerRequest('GET', ['Accept' => ['test/html']]), $requestHandler);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();
        $this->assertSame(self::DEFAULT_RENDERER_RESPONSE, $content);
    }

    public function testWithoutRenderer(): void
    {
        $container = new Container();
        $catcher = $this->createErrorCatcher($container)->withoutRenderers('*/*');
        $requestHandler = (new MockRequestHandler())->setHandleException(new \RuntimeException());
        $response = $catcher->process($this->createServerRequest('GET', ['Accept' => ['test/html']]), $requestHandler);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();
        $this->assertSame(self::DEFAULT_RENDERER_RESPONSE, $content);
    }

    public function testAdvancedAcceptHeader(): void
    {
        $containerId = 'testRenderer';
        $expectedRendererOutput = 'expectedRendererOutput';
        $container = $this->createContainerWithThrowableRenderer($containerId, $expectedRendererOutput);
        $mimeType = 'text/html;version=2';
        $catcher = $this->createErrorCatcher($container)->withRenderer($mimeType, $containerId);
        $requestHandler = (new MockRequestHandler())->setHandleException(new \RuntimeException());
        $response = $catcher->process(
            $this->createServerRequest('GET', ['Accept' => ['text/html', $mimeType]]),
            $requestHandler
        );
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();
        $this->assertNotSame(self::DEFAULT_RENDERER_RESPONSE, $content);
    }

    public function testDefaultContentType(): void
    {
        $expectedRendererOutput = 'expectedRendererOutput';
        $containerId = 'testRenderer';
        $container = $this->createContainerWithThrowableRenderer($containerId, $expectedRendererOutput);
        $catcher = $this->createErrorCatcher($container)
            ->withRenderer('*/*', $containerId);
        $requestHandler = (new MockRequestHandler())->setHandleException(new \RuntimeException());
        $response = $catcher->process(
            $this->createServerRequest('GET', ['Accept' => ['test/test']]),
            $requestHandler
        );
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();
        $this->assertNotSame(self::DEFAULT_RENDERER_RESPONSE, $content);
        $this->assertSame($expectedRendererOutput, $content);
    }

    public function testForceContentType(): void
    {
        $catcher = $this->createErrorCatcher(new Container())->forceContentType('application/json');
        $response = $catcher->process(
            $this->createServerRequest('GET', ['Accept' => ['text/xml']]),
            (new MockRequestHandler())->setHandleException(new \RuntimeException())
        );
        $response->getBody()->rewind();
        $this->assertSame('application/json', $response->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testForceContentTypeSetInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectErrorMessage('The renderer for image/gif is not set.');
        $this->createErrorCatcher(new Container())->forceContentType('image/gif');
    }

    private function createContainerWithThrowableRenderer(string $id, string $expectedOutput): Container
    {
        return new Container(
            [
                $id => new MockThrowableRenderer($expectedOutput),
            ]
        );
    }

    private function createErrorHandler(): ErrorHandler
    {
        $logger = $this->createMock(LoggerInterface::class);
        return new ErrorHandler($logger, new MockThrowableRenderer(self::DEFAULT_RENDERER_RESPONSE));
    }

    private function createServerRequest(string $method, array $headers = []): ServerRequestInterface
    {
        return new ServerRequest([], [], [], [], [], $method, '/', $headers);
    }

    private function createErrorCatcher(Container $container): ErrorCatcher
    {
        return new ErrorCatcher(new ResponseFactory(), $this->createErrorHandler(), $container);
    }
}
