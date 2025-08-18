<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Exception\UserException;

use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;
use Yiisoft\ErrorHandler\Exception\UserException;

use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertSame;

final class UserExceptionTest extends TestCase
{
    public function testUserExceptionInstance(): void
    {
        $exception = new UserException('User error message');

        assertSame('User error message', $exception->getMessage());
        assertInstanceOf(Exception::class, $exception);
    }

    public static function dataIsUserException(): iterable
    {
        yield [true, new UserException()];
        yield [false, new Exception()];
        yield [true, new NotFoundException()];
    }

    #[DataProvider('dataIsUserException')]
    public function testIsUserException(bool $expected, Throwable $exception): void
    {
        assertSame($expected, UserException::isUserException($exception));
    }
}
