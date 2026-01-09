<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Yiisoft\Definitions\DynamicReference;
use Yiisoft\ErrorHandler\Renderer\HtmlRenderer;
use Yiisoft\ErrorHandler\RendererProvider\CompositeRendererProvider;
use Yiisoft\ErrorHandler\RendererProvider\ContentTypeRendererProvider;
use Yiisoft\ErrorHandler\RendererProvider\HeadRendererProvider;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;
use Yiisoft\ErrorHandler\ThrowableResponseFactory;
use Yiisoft\ErrorHandler\ThrowableResponseFactoryInterface;

/**
 * @var array $params
 */

return [
    ThrowableRendererInterface::class => HtmlRenderer::class,
    ThrowableResponseFactoryInterface::class => [
        'class' => ThrowableResponseFactory::class,
        '__construct()' => [
            'rendererProvider' => DynamicReference::to(
                static fn(ContainerInterface $container) => new CompositeRendererProvider(
                    new HeadRendererProvider(),
                    new ContentTypeRendererProvider($container),
                )
            ),
        ],
    ],
];
