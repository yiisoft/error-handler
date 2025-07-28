<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Renderer;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Yiisoft\ErrorHandler\ErrorData;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;
use Yiisoft\Http\Header;

use function sprintf;

/**
 * Formats throwable into plain text string.
 */
final class PlainTextRenderer implements ThrowableRendererInterface
{
    public function __construct(
        private readonly string $contentType = 'text/plain',
    ) {
    }

    public function render(Throwable $t, ?ServerRequestInterface $request = null): ErrorData
    {
        return new ErrorData(
            self::DEFAULT_ERROR_MESSAGE,
            [Header::CONTENT_TYPE => $this->contentType],
        );
    }

    public function renderVerbose(Throwable $t, ?ServerRequestInterface $request = null): ErrorData
    {
        return new ErrorData(
            self::throwableToString($t),
            [Header::CONTENT_TYPE => $this->contentType],
        );
    }

    public static function throwableToString(Throwable $t): string
    {
        return sprintf(
            <<<TEXT
                %s with message "%s"

                in %s:%s

                Stack trace:
                %s
                TEXT,
            $t::class,
            $t->getMessage(),
            $t->getFile(),
            $t->getLine(),
            $t->getTraceAsString()
        );
    }
}
