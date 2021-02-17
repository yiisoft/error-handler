<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * ThrowableRendererInterface converts throwable into error data suitable for displaying.
 */
interface ThrowableRendererInterface
{
    /**
     * Returns error data suitable for displaying in production environment.
     *
     * @param Throwable $t
     * @param ServerRequestInterface|null $request
     *
     * @return ErrorData
     */
    public function render(Throwable $t, ServerRequestInterface $request = null): ErrorData;

    /**
     * Returns error data suitable for displaying in development environment.
     *
     * @param Throwable $t
     * @param ServerRequestInterface|null $request
     *
     * @return ErrorData
     */
    public function renderVerbose(Throwable $t, ServerRequestInterface $request = null): ErrorData;
}
