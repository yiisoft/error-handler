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
    public function __construct(
        private readonly ?string $contentType = null,
    ) {
    }

    public function render(Throwable $t, ?ServerRequestInterface $request = null): ErrorData
    {
        return new ErrorData(
            '',
            $this->addContentTypeHeader([
                'X-Error-Message' => self::DEFAULT_ERROR_MESSAGE,
            ]),
        );
    }

    public function renderVerbose(Throwable $t, ?ServerRequestInterface $request = null): ErrorData
    {
        return new ErrorData(
            '',
            $this->addContentTypeHeader([
                'X-Error-Type' => $t::class,
                'X-Error-Message' => $t->getMessage(),
                'X-Error-Code' => (string) $t->getCode(),
                'X-Error-File' => $t->getFile(),
                'X-Error-Line' => (string) $t->getLine(),
            ]),
        );
    }

    /**
     * @param array<string, string|string[]> $headers
     * @return array<string, string|string[]>
     */
    private function addContentTypeHeader(array $headers): array
    {
        if ($this->contentType !== null) {
            $headers[Header::CONTENT_TYPE] = $this->contentType;
        }
        return $headers;
    }
}
