<?php

declare(strict_types=1);

use Yiisoft\ErrorHandler\HtmlRenderer;
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
];
