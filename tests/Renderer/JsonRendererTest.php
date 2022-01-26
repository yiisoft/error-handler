<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Renderer;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yiisoft\ErrorHandler\Renderer\JsonRenderer;

use function json_encode;
use function md5;
use function pack;

final class JsonRendererTest extends TestCase
{
    public function testRender(): void
    {
        $renderer = new JsonRenderer();
        $data = $renderer->render(new RuntimeException());

        $this->assertSame(json_encode(['message' => JsonRenderer::DEFAULT_ERROR_MESSAGE]), (string) $data);
    }

    public function testRenderVerbose(): void
    {
        $renderer = new JsonRenderer();
        $throwable = new RuntimeException();
        $data = $renderer->renderVerbose($throwable);
        $content = json_encode([
            'type' => RuntimeException::class,
            'message' => $throwable->getMessage(),
            'code' => $throwable->getCode(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'trace' => $throwable->getTrace(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $this->assertSame($content, (string) $data);
    }

    public function testRenderVerboseWithNotUtf8String(): void
    {
        $renderer = new JsonRenderer();
        $throwable = new RuntimeException(pack('H*', md5('binary string')));
        $data = $renderer->renderVerbose($throwable);
        $content = json_encode([
            'type' => RuntimeException::class,
            'message' => $throwable->getMessage(),
            'code' => $throwable->getCode(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'trace' => $throwable->getTrace(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);

        $this->assertSame($content, (string) $data);
    }
}
