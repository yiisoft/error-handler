<?php

declare(strict_types=1);

use Yiisoft\ErrorHandler\Factory\ThrowableResponseFactory;
use Yiisoft\ErrorHandler\Renderer\HtmlRenderer;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;
use Yiisoft\ErrorHandler\ThrowableResponseFactoryInterface;

/**
 * @var array $params
 */

return [
    ThrowableRendererInterface::class => HtmlRenderer::class,
    HtmlRenderer::class => [
        '__construct()' => [
            'solutionProviders' => $params['yiisoft/error-handler']['solutionProviders'],
        ],
    ],
    ThrowableResponseFactoryInterface::class => ThrowableResponseFactory::class,
];
