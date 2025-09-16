<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests;

use HttpSoft\Message\ResponseFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\ErrorHandler\Renderer\HtmlRenderer;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;
use Yiisoft\ErrorHandler\ThrowableResponseFactory;
use Yiisoft\ErrorHandler\ThrowableResponseFactoryInterface;

final class ConfigTest extends TestCase
{
    public function testDiWeb(): void
    {
        $container = $this->createContainer('web');

        $throwableRenderer = $container->get(ThrowableRendererInterface::class);
        $this->assertInstanceOf(HtmlRenderer::class, $throwableRenderer);

        $throwableResponseFactory = $container->get(ThrowableResponseFactoryInterface::class);
        $this->assertInstanceOf(ThrowableResponseFactory::class, $throwableResponseFactory);
    }

    private function createContainer(?string $postfix = null): Container
    {
        return new Container(
            ContainerConfig::create()->withDefinitions([
                ResponseFactoryInterface::class => ResponseFactory::class,
                ...$this->getDiConfig($postfix)
            ])
        );
    }

    private function getDiConfig(?string $postfix = null): array
    {
        return require dirname(__DIR__) . '/config/di' . ($postfix !== null ? '-' . $postfix : '') . '.php';
    }
}
