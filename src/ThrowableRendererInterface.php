<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * ThrowableRendererInterface converts throwable into its string representation
 */
interface ThrowableRendererInterface
{
    /**
     * Convert throwable into its string representation suitable for displaying in production environment
     *
     * @param Throwable $t
     * @param ServerRequestInterface|null $request
     *
     * @return string
     */
    public function render(Throwable $t, ServerRequestInterface $request = null): string;

    /**
     * Convert throwable into its string representation suitable for displaying in development environment
     *
     * @param Throwable $t
     * @param ServerRequestInterface|null $request
     *
     * @return string
     */
    public function renderVerbose(Throwable $t, ServerRequestInterface $request = null): string;
}
