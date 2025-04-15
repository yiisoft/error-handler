<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\RendererProvider;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\ErrorHandler\Renderer\PlainTextRenderer;
use Yiisoft\ErrorHandler\Renderer\XmlRenderer;
use Yiisoft\ErrorHandler\RendererProvider\ClosureRendererProvider;
use Yiisoft\ErrorHandler\Tests\Support\TestHelper;
use Yiisoft\Test\Support\Container\SimpleContainer;

use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertSame;

final class ClosureRendererProviderTest extends TestCase
{
    public function testBase(): void
    {
        $closureRequest = null;

        $provider = new ClosureRendererProvider(
            static function (ServerRequestInterface $request) use (&$closureRequest): string {
                $closureRequest = $request;
                return PlainTextRenderer::class;
            },
            new SimpleContainer([
                PlainTextRenderer::class => new PlainTextRenderer(),
            ]),
        );

        $request = TestHelper::createRequest();
        $renderer = $provider->get($request);

        assertSame($request, $closureRequest);
        assertInstanceOf(PlainTextRenderer::class, $renderer);
    }

    public function testRenderer(): void
    {
        $closureRenderer = new XmlRenderer();
        $provider = new ClosureRendererProvider(
            static fn() => $closureRenderer,
            new SimpleContainer(),
        );

        $renderer = $provider->get(
            TestHelper::createRequest(),
        );

        assertSame($closureRenderer, $renderer);
    }

    public function testNull(): void
    {
        $provider = new ClosureRendererProvider(
            static fn() => null,
            new SimpleContainer(),
        );

        $renderer = $provider->get(
            TestHelper::createRequest(),
        );

        assertNull($renderer);
    }
}
