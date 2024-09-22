<?php

declare(strict_types=1);

use Yiisoft\ErrorHandler\ThrowableHandlerInterface;
use Yiisoft\ErrorHandler\Handler\ThrowableHandler;
use Yiisoft\ErrorHandler\Renderer\HtmlRenderer;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;

/**
 * @var array $params
 */

return [
    ThrowableHandlerInterface::class => ThrowableHandler::class,
    ThrowableRendererInterface::class => HtmlRenderer::class,
];
