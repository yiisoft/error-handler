<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\RendererProvider;

use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\ErrorHandler\Renderer\HeaderRenderer;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;
use Yiisoft\Http\Method;

final class HeadRendererProvider implements RendererProviderInterface
{
    public function get(ServerRequestInterface $request): ?ThrowableRendererInterface
    {
        return $request->getMethod() === Method::HEAD
            ? new HeaderRenderer()
            : null;
    }
}
