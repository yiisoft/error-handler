<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Exception\UserException;

use Exception;
use Yiisoft\ErrorHandler\Exception\UserException;

#[UserException]
final class NotFoundException extends Exception {}
