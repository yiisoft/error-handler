<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Renderer;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yiisoft\ErrorHandler\Renderer\XmlRenderer;

final class XmlRendererTest extends TestCase
{
    public function testRender(): void
    {
        $renderer = new XmlRenderer();
        $data = $renderer->render(new RuntimeException());
        $content = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
        $content .= "\n<error>\n<message>" . XmlRenderer::DEFAULT_ERROR_MESSAGE . "</message>\n</error>";

        $this->assertSame($content, (string) $data);
    }

    public function testRenderVerbose(): void
    {
        $renderer = new XmlRenderer();
        $throwable = new RuntimeException('Some error.');
        $data = $renderer->renderVerbose($throwable);
        $content = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
        $content .= "\n<error>\n";
        $content .= '<type>' . RuntimeException::class . "</type>\n";
        $content .= "<message><![CDATA[Some error.]]></message>\n";
        $content .= "<code><![CDATA[0]]></code>\n";
        $content .= "<file>{$throwable->getFile()}</file>\n";
        $content .= "<line>{$throwable->getLine()}</line>\n";
        $content .= "<trace>{$throwable->getTraceAsString()}</trace>\n";
        $content .= '</error>';

        $this->assertSame($content, (string) $data);
    }
}
