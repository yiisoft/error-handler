<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Renderer;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Yiisoft\ErrorHandler\ErrorData;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;

use function get_class;

/**
 * Formats throwable into plain text string.
 */
final class PlainTextRenderer implements ThrowableRendererInterface
{
    public function render(Throwable $t, ServerRequestInterface $request = null): ErrorData
    {
        return new ErrorData(self::DEFAULT_ERROR_MESSAGE);
    }

    public function renderVerbose(Throwable $t, ServerRequestInterface $request = null): ErrorData
    {
        return new ErrorData(
            $t::class . " with message '{$t->getMessage()}' \n\nin "
            . $t->getFile() . ':' . $t->getLine() . "\n\n"
            . "Stack trace:\n" . $t->getTraceAsString()
        );
    }
}
