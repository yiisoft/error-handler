<?php

declare(strict_types=1);

use Yiisoft\ErrorHandler\Middleware\ExceptionResponder;
use Yiisoft\ErrorHandler\Renderer\HtmlRenderer;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;

/**
 * @var array $params
 */

return [
    HtmlRenderer::class => [
        '__class' => HtmlRenderer::class,
        '__construct()' => [
            $params['yiisoft/error-handler']['htmlRenderer']['templates'],
        ],
    ],

    ThrowableRendererInterface::class => HtmlRenderer::class,

    ExceptionResponder::class => [
        '__construct()' => [
            'exceptionMap' => $params['yiisoft/error-handler']['exceptionResponder']['exceptionMap'],
        ],
    ],
];
