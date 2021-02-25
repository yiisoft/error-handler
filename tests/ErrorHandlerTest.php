<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
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


        $this->errorHandler->handleThrowable($throwable);
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

        $this->errorHandler->handleThrowable($throwable, $throwableRendererMock);
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
        $this->errorHandler->handleThrowable($throwable);
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
        $this->errorHandler->handleThrowable($throwable);
    }

    public function testHandleError(): void
    {
        $array = [];
        $this->errorHandler->register();
        $this->expectException(ErrorException::class);
        $array['undefined'];
        $this->errorHandler->unregister();
    }
}
