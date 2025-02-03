<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Factory;

use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequest;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;
use Yiisoft\ErrorHandler\ErrorHandler;
use Yiisoft\ErrorHandler\Factory\ThrowableResponseFactory;
use Yiisoft\ErrorHandler\HeadersProvider;
use Yiisoft\ErrorHandler\Renderer\HeaderRenderer;
use Yiisoft\ErrorHandler\Renderer\PlainTextRenderer;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;
use Yiisoft\ErrorHandler\ThrowableResponseFactoryInterface;
use Yiisoft\Http\Header;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class ThrowableResponseFactoryTest extends TestCase
{
    public function testHandleWithHeadRequestMethod(): void
    {
        $response = $this
            ->createThrowableResponseFactory()
            ->create(
                $this->createThrowable(),
                $this->createServerRequest('HEAD', ['Accept' => ['test/html']])
            );
        $response
            ->getBody()
            ->rewind();
        $content = $response
            ->getBody()
            ->getContents();

        $this->assertEmpty($content);
        $this->assertSame([HeaderRenderer::DEFAULT_ERROR_MESSAGE], $response->getHeader('X-Error-Message'));
    }

    public function testHandleWithFailAcceptRequestHeader(): void
    {
        $response = $this
            ->createThrowableResponseFactory()
            ->create(
                $this->createThrowable(),
                $this->createServerRequest('GET', ['Accept' => ['text/plain;q=2.0']])
            );
        $response
            ->getBody()
            ->rewind();
        $content = $response
            ->getBody()
            ->getContents();

        $this->assertNotSame(PlainTextRenderer::DEFAULT_ERROR_MESSAGE, $content);
        $this->assertStringContainsString('<html', $content);
    }

    public function testAddedRenderer(): void
    {
        $mimeType = 'test/test';
        $factory = $this
            ->createThrowableResponseFactory()
            ->withRenderer($mimeType, PlainTextRenderer::class);
        $response = $factory->create(
            $this->createThrowable(),
            $this->createServerRequest('GET', ['Accept' => [$mimeType]])
        );
        $response
            ->getBody()
            ->rewind();
        $content = $response
            ->getBody()
            ->getContents();

        $this->assertSame(PlainTextRenderer::DEFAULT_ERROR_MESSAGE, $content);
    }

    public function testThrownExceptionWithRendererIsNotImplementThrowableRendererInterface()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Class "' . self::class . '" does not implement "' . ThrowableRendererInterface::class . '".',
        );
        $this
            ->createThrowableResponseFactory()
            ->withRenderer('test/test', self::class);
    }

    public function testThrownExceptionWithInvalidContentType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid content type.');
        $this
            ->createThrowableResponseFactory()
            ->withRenderer('test invalid content type', PlainTextRenderer::class);
    }

    public function testWithoutRenderers(): void
    {
        $factory = $this
            ->createThrowableResponseFactory()
            ->withoutRenderers();
        $response = $factory->create(
            $this->createThrowable(),
            $this->createServerRequest('GET', ['Accept' => ['test/html']])
        );
        $response
            ->getBody()
            ->rewind();
        $content = $response
            ->getBody()
            ->getContents();

        $this->assertSame(PlainTextRenderer::DEFAULT_ERROR_MESSAGE, $content);
    }

    public function testWithoutRenderer(): void
    {
        $factory = $this
            ->createThrowableResponseFactory()
            ->withoutRenderers('*/*');
        $response = $factory->create(
            $this->createThrowable(),
            $this->createServerRequest('GET', ['Accept' => ['test/html']])
        );
        $response
            ->getBody()
            ->rewind();
        $content = $response
            ->getBody()
            ->getContents();

        $this->assertSame(PlainTextRenderer::DEFAULT_ERROR_MESSAGE, $content);
    }

    public function testAdvancedAcceptHeader(): void
    {
        $contentType = 'text/html;version=2';
        $factory = $this
            ->createThrowableResponseFactory()
            ->withRenderer($contentType, PlainTextRenderer::class);
        $response = $factory->create(
            $this->createThrowable(),
            $this->createServerRequest('GET', ['Accept' => ['text/html', $contentType]])
        );
        $response
            ->getBody()
            ->rewind();
        $content = $response
            ->getBody()
            ->getContents();

        $this->assertSame(PlainTextRenderer::DEFAULT_ERROR_MESSAGE, $content);
    }

    public function testDefaultContentType(): void
    {
        $factory = $this
            ->createThrowableResponseFactory()
            ->withRenderer('*/*', PlainTextRenderer::class);
        $response = $factory->create(
            $this->createThrowable(),
            $this->createServerRequest('GET', ['Accept' => ['test/test']])
        );
        $response
            ->getBody()
            ->rewind();
        $content = $response
            ->getBody()
            ->getContents();

        $this->assertSame(PlainTextRenderer::DEFAULT_ERROR_MESSAGE, $content);
    }

    public function testForceContentType(): void
    {
        $factory = $this
            ->createThrowableResponseFactory()
            ->forceContentType('application/json');
        $response = $factory->create(
            $this->createThrowable(),
            $this->createServerRequest('GET', ['Accept' => ['text/xml']])
        );
        $response
            ->getBody()
            ->rewind();

        $this->assertSame('application/json', $response->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testForceContentTypeSetInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The renderer for image/gif is not set.');
        $this
            ->createThrowableResponseFactory()
            ->forceContentType('image/gif');
    }

    public function testAddedHeaders(): void
    {
        $provider = new HeadersProvider([
            'X-Default' => 'default',
            'Content-Type' => 'incorrect',
        ]);
        $provider->add('X-Test', 'test');
        $provider->add('X-Test2', ['test2', 'test3']);
        $factory = $this
            ->createThrowableResponseFactory(provider: $provider)
            ->withRenderer('*/*', PlainTextRenderer::class);
        $response = $factory->create(
            $this->createThrowable(),
            $this->createServerRequest('GET', ['Accept' => ['test/test']])
        );
        $headers = $response->getHeaders();

        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertNotEquals('incorrect', $headers['Content-Type']);

        $this->assertArrayHasKey('X-Default', $headers);
        $this->assertEquals(['default'], $headers['X-Default']);
        $this->assertArrayHasKey('X-Test', $headers);
        $this->assertEquals(['test'], $headers['X-Test']);
        $this->assertArrayHasKey('X-Test2', $headers);
        $this->assertEquals(['test2', 'test3'], $headers['X-Test2']);
    }

    private function createThrowableResponseFactory(
        ?HeadersProvider $provider = null,
    ): ThrowableResponseFactoryInterface {
        $container = new SimpleContainer([], fn (string $className): object => new $className());
        return new ThrowableResponseFactory(
            new ResponseFactory(),
            $this->createErrorHandler(),
            $container,
            $provider ?? new HeadersProvider()
        );
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

    private function createThrowable(): Throwable
    {
        return new RuntimeException();
    }
}
