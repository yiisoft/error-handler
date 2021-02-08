<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Renderer;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Yiisoft\ErrorHandler\ThrowableRenderer;

/**
 * Formats exception into plain text string.
 */
final class PlainTextRenderer extends ThrowableRenderer
{
    public function render(Throwable $t, ServerRequestInterface $request = null): string
    {
        return 'An internal server error occurred';
    }

    public function renderVerbose(Throwable $t, ServerRequestInterface $request = null): string
    {
        return $this->convertThrowableToVerboseString($t);
    }
}
