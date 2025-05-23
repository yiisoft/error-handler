<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Renderer;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Yiisoft\ErrorHandler\ErrorData;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;
use Yiisoft\Http\Header;

use function json_encode;

/**
 * Formats throwable into JSON string.
 */
final class JsonRenderer implements ThrowableRendererInterface
{
    private const CONTENT_TYPE = 'application/json';

    public function render(Throwable $t, ?ServerRequestInterface $request = null): ErrorData
    {
        return new ErrorData(
            json_encode(
                [
                    'message' => self::DEFAULT_ERROR_MESSAGE,
                ],
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES
            ),
            [Header::CONTENT_TYPE => self::CONTENT_TYPE],
        );
    }

    public function renderVerbose(Throwable $t, ?ServerRequestInterface $request = null): ErrorData
    {
        return new ErrorData(
            json_encode(
                [
                    'type' => $t::class,
                    'message' => $t->getMessage(),
                    'code' => $t->getCode(),
                    'file' => $t->getFile(),
                    'line' => $t->getLine(),
                    'trace' => $t->getTrace(),
                ],
                JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR
            ),
            [Header::CONTENT_TYPE => self::CONTENT_TYPE],
        );
    }
}
