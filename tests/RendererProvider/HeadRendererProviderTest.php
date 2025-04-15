<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\RendererProvider;

use HttpSoft\Message\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yiisoft\ErrorHandler\Renderer\HeaderRenderer;
use Yiisoft\ErrorHandler\RendererProvider\HeadRendererProvider;
use Yiisoft\ErrorHandler\Tests\Support\TestHelper;

use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertSame;

final class HeadRendererProviderTest extends TestCase
{
    #[TestWith(['GET'])]
    #[TestWith(['POST'])]
    #[TestWith(['DELETE'])]
    #[TestWith(['PATCH'])]
    public function testNotHeadRequest(string $requestMethod): void
    {
        $provider = new HeadRendererProvider();

        $renderer = $provider->get(
            TestHelper::createRequest($requestMethod),
        );

        assertNull($renderer);
    }

    public static function dataHeadRequest(): iterable
    {
        yield ['', []];
        yield ['text/html', ['Accept' => ['text/html']]];
        yield ['text/html', ['Accept' => ['text/html;q=0.5']]];
        yield ['', ['Accept' => ['text/html;q=x']]];
    }

    #[DataProvider('dataHeadRequest')]
    public function testHeadRequest(string $expectedContentType, array $headers): void
    {
        $provider = new HeadRendererProvider();

        $renderer = $provider->get(
            TestHelper::createRequest('HEAD', headers: $headers),
        );

        assertInstanceOf(HeaderRenderer::class, $renderer);

        $response = $renderer
            ->render(new RuntimeException())
            ->addToResponse(new Response());

        assertSame($expectedContentType, $response->getHeaderLine('Content-Type'));
    }
}
