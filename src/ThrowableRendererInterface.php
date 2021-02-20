<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * ThrowableRendererInterface converts throwable into error data suitable for adding it to response.
 */
interface ThrowableRendererInterface
{
    public const DEFAULT_ERROR_MESSAGE = 'An internal server error occurred.';

    /**
     * Returns error data suitable for adding it to response in production environment.
     *
     * @param Throwable $t
     * @param ServerRequestInterface|null $request
     *
     * @return ErrorData
     */
    public function render(Throwable $t, ServerRequestInterface $request = null): ErrorData;

    /**
     * Returns error data suitable for adding it to response in development environment.
     *
     * @param Throwable $t
     * @param ServerRequestInterface|null $request
     *
     * @return ErrorData
     */
    public function renderVerbose(Throwable $t, ServerRequestInterface $request = null): ErrorData;
}
