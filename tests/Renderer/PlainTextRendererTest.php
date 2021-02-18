<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Renderer;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yiisoft\ErrorHandler\Renderer\PlainTextRenderer;

final class PlainTextRendererTest extends TestCase
{
    public function testRender(): void
    {
        $renderer = new PlainTextRenderer();
        $data = $renderer->render(new RuntimeException());

        $this->assertSame(PlainTextRenderer::DEFAULT_ERROR_MESSAGE, (string) $data);
    }

    public function testRenderVerbose(): void
    {
        $renderer = new PlainTextRenderer();
        $throwable = new RuntimeException();
        $data = $renderer->renderVerbose($throwable);
        $content = RuntimeException::class . " with message '{$throwable->getMessage()}' \n\nin "
            . $throwable->getFile() . ':' . $throwable->getLine() . "\n\n"
            . "Stack trace:\n" . $throwable->getTraceAsString()
        ;

        $this->assertSame($content, (string) $data);
    }
}
