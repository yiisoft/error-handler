<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Yiisoft\ErrorHandler\Renderer\HtmlRenderer;

final class HtmlRendererTest extends TestCase
{
    private const CUSTOM_SETTING = [
        'verboseTemplate' => __DIR__ . '/test-template-verbose.php',
        'template' => __DIR__ . '/test-template-non-verbose.php',
    ];

    public function testNonVerboseOutput(): void
    {
        $renderer = new HtmlRenderer();
        $exceptionMessage = 'exception-test-message';
        $exception = new RuntimeException($exceptionMessage);
        $renderedOutput = $renderer->render($exception, $this->getServerRequestMock());

        $this->assertStringContainsString('<html', $renderedOutput);
        $this->assertStringNotContainsString(RuntimeException::class, $renderedOutput);
        $this->assertStringNotContainsString($exceptionMessage, $renderedOutput);
    }

    public function testVerboseOutput(): void
    {
        $renderer = new HtmlRenderer();
        $exceptionMessage = 'exception-test-message';
        $exception = new RuntimeException($exceptionMessage);
        $renderedOutput = $renderer->renderVerbose($exception, $this->getServerRequestMock());

        $this->assertStringContainsString('<html', $renderedOutput);
        $this->assertStringContainsString(RuntimeException::class, $renderedOutput);
        $this->assertStringContainsString($exceptionMessage, $renderedOutput);
    }

    public function testNonVerboseOutputWithCustomTemplate(): void
    {
        $templateFileContents = '<html><?php echo $throwable->getMessage();?></html>';
        $this->createTestTemplate(self::CUSTOM_SETTING['template'], $templateFileContents);

        $renderer = new HtmlRenderer(self::CUSTOM_SETTING);
        $exceptionMessage = 'exception-test-message';
        $exception = new RuntimeException($exceptionMessage);

        $renderedOutput = $renderer->render($exception, $this->getServerRequestMock());
        $this->assertStringContainsString("<html>$exceptionMessage</html>", $renderedOutput);
    }

    public function testVerboseOutputWithCustomTemplate(): void
    {
        $templateFileContents = '<html><?php echo $throwable->getMessage();?></html>';
        $this->createTestTemplate(self::CUSTOM_SETTING['verboseTemplate'], $templateFileContents);

        $renderer = new HtmlRenderer(self::CUSTOM_SETTING);
        $exceptionMessage = 'exception-test-message';
        $exception = new RuntimeException($exceptionMessage);

        $renderedOutput = $renderer->renderVerbose($exception, $this->getServerRequestMock());
        $this->assertStringContainsString("<html>$exceptionMessage</html>", $renderedOutput);
    }

    public function testRenderTemplateThrowsExceptionWhenTemplateFileNotExists(): void
    {
        $renderer = new HtmlRenderer(['template' => '_not_found_.php']);
        $exception = new Exception();

        $this->expectException(RuntimeException::class);
        $renderer->render($exception, $this->getServerRequestMock());
    }

    public function tearDown(): void
    {
        parent::tearDown();
        foreach (self::CUSTOM_SETTING as $template) {
            if (file_exists($template)) {
                $this->removeTestTemplate($template);
            }
        }
    }

    private function getServerRequestMock(): ServerRequestInterface
    {
        $acceptHeader = [
            'text/html',
        ];
        $serverRequestMock = $this->createMock(ServerRequestInterface::class);
        $serverRequestMock
            ->method('getHeader')
            ->with('Accept')
            ->willReturn($acceptHeader);

        $serverRequestMock
            ->method('getHeaders')
            ->willReturn(
                [
                    'Accept' => $acceptHeader,
                ]
            );

        $serverRequestMock
            ->method('getMethod')
            ->willReturn('GET');

        return $serverRequestMock;
    }

    private function createTestTemplate(string $path, string $templateContents): void
    {
        if (!file_put_contents($path, $templateContents)) {
            throw new RuntimeException(sprintf('Unable to create file at path %s', $path));
        }
    }

    private function removeTestTemplate(string $path): void
    {
        unlink($path);
    }
}
