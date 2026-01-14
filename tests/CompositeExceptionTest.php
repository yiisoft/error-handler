<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\ErrorHandler\CompositeException;
use Exception;

final class CompositeExceptionTest extends TestCase
{
    public function testExceptions()
    {
        $exception1 = new Exception('Exception 1', 123, new Exception('Previous exception'));
        $exception2 = new Exception('Exception 2');
        $exception = new CompositeException(
            $exception1,
            $exception2,
        );

        $this->assertSame('Exception 1', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
        $this->assertSame($exception1, $exception->getPrevious());
        $this->assertSame($exception1, $exception->getFirstException());
        $this->assertSame([$exception2], $exception->getPreviousExceptions());
    }

    public function testOnlyOneException()
    {
        $exception1 = new Exception('Exception 1', 123, new Exception('Previous exception'));
        $exception = new CompositeException($exception1);

        $this->assertSame('Exception 1', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
        $this->assertSame($exception1, $exception->getPrevious());
        $this->assertSame($exception1, $exception->getFirstException());
        $this->assertSame([], $exception->getPreviousExceptions());
    }
}
