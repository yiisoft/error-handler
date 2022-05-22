<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Renderer;

use HttpSoft\Message\Response;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yiisoft\ErrorHandler\Renderer\HeaderRenderer;

final class HeaderRendererTest extends TestCase
{
    public function testRender(): void
    {
        $renderer = new HeaderRenderer();
        $data = $renderer->render(new RuntimeException());
        $response = $data->addToResponse(new Response());
        $response
            ->getBody()
            ->rewind();

        $this->assertEmpty($response
            ->getBody()
            ->getContents());
        $this->assertSame([HeaderRenderer::DEFAULT_ERROR_MESSAGE], $response->getHeader('X-Error-Message'));
    }

    public function testRenderVerbose(): void
    {
        $renderer = new HeaderRenderer();
        $throwable = new RuntimeException();
        $data = $renderer->renderVerbose($throwable);
        $response = $data->addToResponse(new Response());
        $response
            ->getBody()
            ->rewind();

        $this->assertEmpty($response
            ->getBody()
            ->getContents());
        $this->assertSame([RuntimeException::class], $response->getHeader('X-Error-Type'));
        $this->assertSame([$throwable->getMessage()], $response->getHeader('X-Error-Message'));
        $this->assertSame([(string) $throwable->getCode()], $response->getHeader('X-Error-Code'));
        $this->assertSame([$throwable->getFile()], $response->getHeader('X-Error-File'));
        $this->assertSame([(string) $throwable->getLine()], $response->getHeader('X-Error-Line'));
    }
}
