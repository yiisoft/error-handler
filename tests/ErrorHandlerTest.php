<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;
use Yiisoft\ErrorHandler\ErrorHandler;
use Yiisoft\ErrorHandler\Exception\ErrorException;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;

final class ErrorHandlerTest extends TestCase
{
    private ErrorHandler $errorHandler;
    private LoggerInterface $loggerMock;
    private ThrowableRendererInterface $throwableRendererMock;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->throwableRendererMock = $this->createMock(ThrowableRendererInterface::class);
        $this->errorHandler = new ErrorHandler($this->loggerMock, $this->throwableRendererMock);
        $this->errorHandler->memoryReserveSize(0);
    }

    public function testHandleThrowableCallsDefaultRendererWhenNonePassed(): void
    {
        $throwable = new RuntimeException();

        $this
            ->throwableRendererMock
            ->expects($this->once())
            ->method('render')
            ->with($throwable);


        $this->errorHandler->handle($throwable);
    }

    public function testHandleThrowableCallsPassedRenderer(): void
    {
        $throwable = new RuntimeException();
        $throwableRendererMock = $this->createMock(ThrowableRendererInterface::class);

        $this
            ->throwableRendererMock
            ->expects($this->never())
            ->method('render')
            ->with($throwable);

        $throwableRendererMock
            ->expects($this->once())
            ->method('render')
            ->with($throwable);

        $this->errorHandler->handle($throwable, $throwableRendererMock);
    }

    public function testHandleThrowableWithExposedDetailsCallsRenderVerbose(): void
    {
        $throwable = new RuntimeException();
        $this
            ->throwableRendererMock
            ->expects($this->once())
            ->method('renderVerbose')
            ->with($throwable);

        $this->errorHandler->debug();
        $this->errorHandler->handle($throwable);
    }

    public function testHandleThrowableWithoutExposedDetailsCallsRender(): void
    {
        $throwable = new RuntimeException();
        $this
            ->throwableRendererMock
            ->expects($this->once())
            ->method('render')
            ->with($throwable);

        $this->errorHandler->debug(false);
        $this->errorHandler->handle($throwable);
    }

    public function testHandleError(): void
    {
        $array = [];
        $this->errorHandler->register();
        $this->expectException(ErrorException::class);
        $array['undefined'];
        $this->errorHandler->unregister();
    }

    public function testHandleErrorWithCatching(): void
    {
        $this->errorHandler->register();
        $array = ['type' => 'undefined'];

        $exception = null;
        try {
            $array['undefined'];
        } catch (Throwable $exception) {
        }

        $this->assertInstanceOf(ErrorException::class, $exception);
        $this->assertFalse($exception::isFatalError($array));
        $this->assertNull($exception->getSolution());
        $this->assertNotEmpty($exception->getBacktrace());

        $this->errorHandler->unregister();
    }
}
