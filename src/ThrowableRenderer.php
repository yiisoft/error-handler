<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler;

use Throwable;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

abstract class ThrowableRenderer implements ThrowableRendererInterface
{
    protected function getThrowableName(Throwable $t): string
    {
        $name = get_class($t);

        if ($t instanceof FriendlyExceptionInterface) {
            $name = $t->getName() . ' (' . $name . ')';
        }

        return $name;
    }

    protected function convertThrowableToVerboseString(Throwable $t): string
    {
        return $this->getThrowableName($t) . " with message '{$t->getMessage()}' \n\nin "
            . $t->getFile() . ':' . $t->getLine() . "\n\n"
            . "Stack trace:\n" . $t->getTraceAsString();
    }
}
