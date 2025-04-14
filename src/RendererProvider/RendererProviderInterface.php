<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\RendererProvider;

use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;

/**
 * Interface that provides a way to get a `ThrowableRendererInterface` implementation based on the request.
 */
interface RendererProviderInterface
{
    /**
     * @param ServerRequestInterface $request The server request.
     *
     * @return ThrowableRendererInterface|null The `ThrowableRendererInterface` implementation or null if not found.
     */
    public function get(ServerRequestInterface $request): ?ThrowableRendererInterface;
}
