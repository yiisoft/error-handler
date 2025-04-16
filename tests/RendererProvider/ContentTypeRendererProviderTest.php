<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\RendererProvider;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yiisoft\ErrorHandler\Renderer\HtmlRenderer;
use Yiisoft\ErrorHandler\Renderer\JsonRenderer;
use Yiisoft\ErrorHandler\Renderer\PlainTextRenderer;
use Yiisoft\ErrorHandler\Renderer\XmlRenderer;
use Yiisoft\ErrorHandler\RendererProvider\ContentTypeRendererProvider;
use Yiisoft\ErrorHandler\Tests\Support\TestHelper;
use Yiisoft\Test\Support\Container\SimpleContainer;

use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertNull;

final class ContentTypeRendererProviderTest extends TestCase
{
    public static function dataBase(): iterable
    {
        yield [JsonRenderer::class, 'application/json'];
        yield [XmlRenderer::class, 'application/xml'];
        yield [XmlRenderer::class, 'text/xml'];
        yield [PlainTextRenderer::class, 'text/plain'];
        yield [HtmlRenderer::class, 'text/html'];
        yield [HtmlRenderer::class, '*/*'];
    }

    #[DataProvider('dataBase')]
    public function testBase(string $expectedRendererClass, string $accept): void
    {
        $provider = new ContentTypeRendererProvider(
            new SimpleContainer([
                JsonRenderer::class => new JsonRenderer(),
                XmlRenderer::class => new XmlRenderer(),
                PlainTextRenderer::class => new PlainTextRenderer(),
                HtmlRenderer::class => new HtmlRenderer(),
            ]),
        );

        $renderer = $provider->get(
            TestHelper::createRequest(headers: ['Accept' => $accept]),
        );

        assertInstanceOf($expectedRendererClass, $renderer);
    }

    public function testCustomRenderer(): void
    {
        $provider = new ContentTypeRendererProvider(
            new SimpleContainer([
                PlainTextRenderer::class => new PlainTextRenderer(),
            ]),
            ['text/new' => PlainTextRenderer::class],
        );

        $renderer = $provider->get(
            TestHelper::createRequest(headers: ['Accept' => 'text/new']),
        );

        assertInstanceOf(PlainTextRenderer::class, $renderer);
    }

    public function testInvalidAccept(): void
    {
        $provider = new ContentTypeRendererProvider(
            new SimpleContainer(),
        );

        $renderer = $provider->get(
            TestHelper::createRequest(headers: ['Accept' => 'text/html;q=x']),
        );

        assertNull($renderer);
    }

    public function testNonExistRenderer(): void
    {
        $provider = new ContentTypeRendererProvider(
            new SimpleContainer(),
        );

        $renderer = $provider->get(
            TestHelper::createRequest(headers: ['Accept' => 'text/unknown']),
        );

        assertNull($renderer);
    }
}
