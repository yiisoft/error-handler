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
        $expectedContent = sprintf(
            <<<TEXT
                %s with message "%s"

                in %s:%s

                Stack trace:
                %s
                TEXT,
            $throwable::class,
            $throwable->getMessage(),
            $throwable->getFile(),
            $throwable->getLine(),
            $throwable->getTraceAsString()
        );

        $data = $renderer->renderVerbose($throwable);
        $this->assertSame($expectedContent, (string) $data);
        $this->assertSame($expectedContent, PlainTextRenderer::throwableToString($throwable));
    }
}
