<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\RendererProvider;

use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;

interface RendererProviderInterface
{
    public function get(ServerRequestInterface $request): ?ThrowableRendererInterface;
}
