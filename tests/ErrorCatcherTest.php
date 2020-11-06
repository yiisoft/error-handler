<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Di\Container;
use Yiisoft\ErrorHandler\ErrorHandler;
use Yiisoft\ErrorHandler\ErrorCatcher;

class ErrorCatcherTest extends TestCase
{
    private const DEFAULT_RENDERER_RESPONSE = 'default-renderer-test';

    public function testAddedRenderer(): void
    {
        $expectedRendererOutput = 'expectedRendererOutput';
        $containerId = 'testRenderer';
        $container = $this->getContainerWithThrowableRenderer($containerId, $expectedRendererOutput);
        $mimeType = 'test/test';
        $catcher = $this->getErrorCatcher($container)->withRenderer($mimeType, $containerId);
        $requestHandler = (new MockRequestHandler())->setHandleExcaption(new \RuntimeException());
        $response = $catcher->process(new ServerRequest('GET', '/', ['Accept' => [$mimeType]]), $requestHandler);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();
        $this->assertNotSame(self::DEFAULT_RENDERER_RESPONSE, $content);
        $this->assertSame($expectedRendererOutput, $content);
    }

    public function testThrownExceptionWithNotExistsRenderer()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectErrorMessage('The renderer "InvalidRendererClass" cannot be found.');

        $this->getErrorCatcher(new Container())->withRenderer('test/test', \InvalidRendererClass::class);
    }

    public function testThrownExceptionWithInvalidMimeType()
    {
        $containerId = 'testRenderer';
        $container = $this->getContainerWithThrowableRenderer($containerId, '');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectErrorMessage('Invalid mime type.');

        $this->getErrorCatcher($container)->withRenderer('test invalid mimeType', $containerId);
    }

    public function testWithoutRenderers(): void
    {
        $container = new Container();
        $catcher = $this->getErrorCatcher($container)->withoutRenderers();
        $requestHandler = (new MockRequestHandler())->setHandleExcaption(new \RuntimeException());
        $response = $catcher->process(new ServerRequest('GET', '/', ['Accept' => ['test/html']]), $requestHandler);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();
        $this->assertSame(self::DEFAULT_RENDERER_RESPONSE, $content);
    }

    public function testWithoutRenderer(): void
    {
        $container = new Container();
        $catcher = $this->getErrorCatcher($container)->withoutRenderers('*/*');
        $requestHandler = (new MockRequestHandler())->setHandleExcaption(new \RuntimeException());
        $response = $catcher->process(new ServerRequest('GET', '/', ['Accept' => ['test/html']]), $requestHandler);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();
        $this->assertSame(self::DEFAULT_RENDERER_RESPONSE, $content);
    }

    public function testAdvancedAcceptHeader(): void
    {
        $containerId = 'testRenderer';
        $expectedRendererOutput = 'expectedRendererOutput';
        $container = $this->getContainerWithThrowableRenderer($containerId, $expectedRendererOutput);
        $mimeType = 'text/html;version=2';
        $catcher = $this->getErrorCatcher($container)->withRenderer($mimeType, $containerId);
        $requestHandler = (new MockRequestHandler())->setHandleExcaption(new \RuntimeException());
        $response = $catcher->process(
            new ServerRequest('GET', '/', ['Accept' => ['text/html', $mimeType]]),
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
        $container = $this->getContainerWithThrowableRenderer($containerId, $expectedRendererOutput);
        $catcher = $this->getErrorCatcher($container)
            ->withRenderer('*/*', $containerId);
        $requestHandler = (new MockRequestHandler())->setHandleExcaption(new \RuntimeException());
        $response = $catcher->process(
            new ServerRequest('GET', '/', ['Accept' => ['test/test']]),
            $requestHandler
        );
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();
        $this->assertNotSame(self::DEFAULT_RENDERER_RESPONSE, $content);
        $this->assertSame($expectedRendererOutput, $content);
    }

    public function testForceContentType(): void
    {
        $catcher = $this->getErrorCatcher(new Container())->forceContentType('application/json');
        $response = $catcher->process(
            new ServerRequest('GET', '/', ['Accept' => ['text/xml']]),
            (new MockRequestHandler())->setHandleExcaption(new \RuntimeException())
        );
        $response->getBody()->rewind();
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testForceContentTypeSetInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectErrorMessage('The renderer for image/gif is not set.');
        $this->getErrorCatcher(new Container())->forceContentType('image/gif');
    }

    private function getContainerWithThrowableRenderer(string $id, string $expectedOutput): Container
    {
        return new Container(
            [
                $id => new MockThrowableRenderer($expectedOutput)
            ]
        );
    }

    private function getErrorHandler(): ErrorHandler
    {
        $logger = $this->createMock(LoggerInterface::class);
        return new ErrorHandler($logger, new MockThrowableRenderer(self::DEFAULT_RENDERER_RESPONSE));
    }

    private function getFactory(): ResponseFactoryInterface
    {
        return new Psr17Factory();
    }

    private function getErrorCatcher(Container $container): ErrorCatcher
    {
        return new ErrorCatcher($this->getFactory(), $this->getErrorHandler(), $container);
    }
}
