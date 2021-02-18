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
    }

    public function testHandleCaughtThrowableCallsDefaultRendererWhenNonePassed(): void
    {
        $throwable = new RuntimeException();

        $this
            ->throwableRendererMock
            ->expects($this->once())
            ->method('render')
            ->with($throwable);


        $this->errorHandler->handleCaughtThrowable($throwable);
    }

    public function testHandleCaughtThrowableCallsPassedRenderer(): void
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

        $this->errorHandler->handleCaughtThrowable($throwable, $throwableRendererMock);
    }

    public function testHandleCaughtThrowableWithExposedDetailsCallsRenderVerbose(): void
    {
        $throwable = new RuntimeException();
        $this
            ->throwableRendererMock
            ->expects($this->once())
            ->method('renderVerbose')
            ->with($throwable);

        $this->errorHandler->debug();
        $this->errorHandler->handleCaughtThrowable($throwable);
    }

    public function testHandleCaughtThrowableWithoutExposedDetailsCallsRender(): void
    {
        $throwable = new RuntimeException();
        $this
            ->throwableRendererMock
            ->expects($this->once())
            ->method('render')
            ->with($throwable);

        $this->errorHandler->debug(false);
        $this->errorHandler->handleCaughtThrowable($throwable);
    }

    public function testHandleError(): void
    {
        $array = [];
        $this->errorHandler->register();
        $this->expectException(ErrorException::class);
        $array['undefined'];
        $this->errorHandler->unregister();
    }

    public function testHandleErrorIfErrorCodeIsIncludedInErrorReporting(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Some error.');
        $this->errorHandler->handleError(1, 'Some error.', __FILE__, __LINE__);
    }

    public function testHandleErrorIfErrorCodeIsNotIncludedInErrorReporting(): void
    {
        $this->expectOutputString('');
        $this->errorHandler->handleError(0, 'Some error.', __FILE__, __LINE__);
    }
}
