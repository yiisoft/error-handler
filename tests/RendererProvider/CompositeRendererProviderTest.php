<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\RendererProvider;

use PHPUnit\Framework\TestCase;
use Yiisoft\ErrorHandler\Renderer\HeaderRenderer;
use Yiisoft\ErrorHandler\RendererProvider\ClosureRendererProvider;
use Yiisoft\ErrorHandler\RendererProvider\CompositeRendererProvider;
use Yiisoft\ErrorHandler\RendererProvider\ContentTypeRendererProvider;
use Yiisoft\ErrorHandler\RendererProvider\HeadRendererProvider;
use Yiisoft\ErrorHandler\Tests\Support\TestHelper;
use Yiisoft\Test\Support\Container\SimpleContainer;

use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertNull;

final class CompositeRendererProviderTest extends TestCase
{
    public function testBase(): void
    {
        $provider = new CompositeRendererProvider(
            new ContentTypeRendererProvider(new SimpleContainer()),
            new HeadRendererProvider(),
        );

        $renderer = $provider->get(
            TestHelper::createRequest('HEAD'),
        );

        assertInstanceOf(HeaderRenderer::class, $renderer);
    }

    public function testNotFound(): void
    {
        $provider = new CompositeRendererProvider(
            new ContentTypeRendererProvider(new SimpleContainer()),
            new ClosureRendererProvider(
                static fn() => null,
                new SimpleContainer(),
            ),
        );

        $renderer = $provider->get(
            TestHelper::createRequest('HEAD'),
        );

        assertNull($renderer);
    }
}
