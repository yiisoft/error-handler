<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Renderer;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Yiisoft\ErrorHandler\ErrorData;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;
use Yiisoft\Http\Header;

/**
 * Formats throwable into HTTP headers.
 */
final class HeaderRenderer implements ThrowableRendererInterface
{
    private const CONTENT_TYPE = '*/*';

    public function render(Throwable $t, ?ServerRequestInterface $request = null): ErrorData
    {
        return new ErrorData('', [
            'X-Error-Message' => self::DEFAULT_ERROR_MESSAGE,
            Header::CONTENT_TYPE => self::CONTENT_TYPE,
        ]);
    }

    public function renderVerbose(Throwable $t, ?ServerRequestInterface $request = null): ErrorData
    {
        return new ErrorData('', [
            'X-Error-Type' => $t::class,
            'X-Error-Message' => $t->getMessage(),
            'X-Error-Code' => (string) $t->getCode(),
            'X-Error-File' => $t->getFile(),
            'X-Error-Line' => (string) $t->getLine(),
            Header::CONTENT_TYPE => self::CONTENT_TYPE,
        ]);
    }
}
