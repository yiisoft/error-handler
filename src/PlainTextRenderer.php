<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler;

final class PlainTextRenderer extends ThrowableRenderer
{
    public function render(\Throwable $t): string
    {
        return 'An internal server error occurred';
    }

    public function renderVerbose(\Throwable $t): string
    {
        return $this->convertThrowableToVerboseString($t);
    }
}
