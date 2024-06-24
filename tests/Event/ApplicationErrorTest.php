<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Event;

use LogicException;
use PHPUnit\Framework\TestCase;
use Yiisoft\ErrorHandler\Event\ApplicationError;

final class ApplicationErrorTest extends TestCase
{
    public function testBase(): void
    {
        $exception = new LogicException();

        $error = new ApplicationError($exception);

        $this->assertSame($exception, $error->getThrowable());
    }
}
