<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Support;

use Exception;
use Yiisoft\FriendlyException\Attribute\FriendlyException;

#[FriendlyException(name: 'Test Exception Name', solution: 'This is a test solution for an exception.')]
final class TestExceptionWithAttribute extends Exception
{
    public function __construct()
    {
        parent::__construct('This is a test exception with attribute.');
    }
} 