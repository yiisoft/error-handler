<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Renderer;

use Exception;
use HttpSoft\Message\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use ReflectionObject;
use RuntimeException;
use Yiisoft\ErrorHandler\Exception\ErrorException;
use Yiisoft\ErrorHandler\Renderer\HtmlRenderer;

use function dirname;
use function file_exists;
use function file_put_contents;
use function fopen;
use function unlink;

final class HtmlRendererTest extends TestCase
{
    private const CUSTOM_SETTING = [
        'verboseTemplate' => __DIR__ . '/test-template-verbose.php',
        'template' => __DIR__ . '/test-template-non-verbose.php',
    ];

    protected function tearDown(): void
    {
        foreach (self::CUSTOM_SETTING as $template) {
            if (file_exists($template)) {
                unlink($template);
            }
        }
    }

    public function testNonVerboseOutput(): void
    {
        $renderer = new HtmlRenderer();
        $exceptionMessage = 'exception-test-message';
        $exception = new RuntimeException($exceptionMessage);
        $errorData = $renderer->render($exception, $this->createServerRequestMock());

        $this->assertStringContainsString('<html', (string) $errorData);
        $this->assertStringNotContainsString(RuntimeException::class, (string) $errorData);
        $this->assertStringNotContainsString($exceptionMessage, (string) $errorData);
    }

    public function testVerboseOutput(): void
    {
        $renderer = new HtmlRenderer();
        $exceptionMessage = 'exception-test-message';
        $exception = new RuntimeException($exceptionMessage);
        $errorData = $renderer->renderVerbose($exception, $this->createServerRequestMock());

        $this->assertStringContainsString('<html', (string) $errorData);
        $this->assertStringContainsString(RuntimeException::class, (string) $errorData);
        $this->assertStringContainsString($exceptionMessage, (string) $errorData);
    }

    public function testNonVerboseOutputWithCustomTemplate(): void
    {
        $templateFileContents = '<html><?php echo $throwable->getMessage();?></html>';
        $this->createTestTemplate(self::CUSTOM_SETTING['template'], $templateFileContents);

        $renderer = new HtmlRenderer(self::CUSTOM_SETTING);
        $exceptionMessage = 'exception-test-message';
        $exception = new RuntimeException($exceptionMessage);

        $errorData = $renderer->render($exception, $this->createServerRequestMock());
        $this->assertStringContainsString("<html>$exceptionMessage</html>", (string) $errorData);
    }

    public function testVerboseOutputWithCustomTemplate(): void
    {
        $templateFileContents = '<html><?php echo $throwable->getMessage();?></html>';
        $this->createTestTemplate(self::CUSTOM_SETTING['verboseTemplate'], $templateFileContents);

        $renderer = new HtmlRenderer(self::CUSTOM_SETTING);
        $exceptionMessage = 'exception-test-message';
        $exception = new RuntimeException($exceptionMessage);

        $errorData = $renderer->renderVerbose($exception, $this->createServerRequestMock());
        $this->assertStringContainsString("<html>$exceptionMessage</html>", (string) $errorData);
    }

    public function testRenderTemplateThrowsExceptionWhenTemplateFileNotExists(): void
    {
        $renderer = new HtmlRenderer(['template' => '_not_found_.php']);
        $exception = new Exception();

        $this->expectException(RuntimeException::class);
        $renderer->render($exception, $this->createServerRequestMock());
    }

    public function testRenderTemplateThrowsExceptionWhenFailureInTemplate(): void
    {
        $this->createTestTemplate(self::CUSTOM_SETTING['verboseTemplate'], '<html><?php throw $throwable;?></html>');
        $renderer = new HtmlRenderer(self::CUSTOM_SETTING);
        $exceptionMessage = 'Template error.';
        $exception = new RuntimeException($exceptionMessage);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($exceptionMessage);
        $renderer->renderVerbose($exception, $this->createServerRequestMock());
    }

    public function testRenderPreviousExceptions(): void
    {
        $previousExceptionMessage = 'Test Previous Exception.';
        $exception = new RuntimeException('Some error.', 0, new Exception($previousExceptionMessage));
        $templateFileContents = '<?php echo $this->renderPreviousExceptions($throwable); ?>';
        $this->createTestTemplate(self::CUSTOM_SETTING['verboseTemplate'], $templateFileContents);
        $renderer = new HtmlRenderer(self::CUSTOM_SETTING);

        $errorData = $renderer->renderVerbose($exception, $this->createServerRequestMock());
        $this->assertStringContainsString($previousExceptionMessage, (string) $errorData);
    }

    public function testRenderCallStack(): void
    {
        $renderer = new HtmlRenderer(self::CUSTOM_SETTING);

        $this->assertStringContainsString(
            'new RuntimeException(&#039;Some error.&#039;)',
            $renderer->renderCallStack(new RuntimeException('Some error.'))
        );
    }

    public function testRenderCallStackItemIfFileIsNotExistAndLineMoreZero(): void
    {
        $this->assertEmpty($this->invokeMethod(new HtmlRenderer(), 'renderCallStackItem', [
            'file' => 'not-exist',
            'line' => 1,
            'class' => null,
            'function' => null,
            'args' => [],
            'index' => 1,
            'isVendorFile' => false,
        ]));
    }

    public function testRenderRequest(): void
    {
        $renderer = new HtmlRenderer();
        $output = $renderer->renderRequest($this->createServerRequestMock());

        $this->assertSame("<pre>GET https:/example.com\nAccept: text/html</pre>", $output);
    }

    public function testRenderCurlForFailRequest(): void
    {
        $renderer = new HtmlRenderer();
        $output = $renderer->renderCurl($this->createServerRequestMock());

        $this->assertSame('Error generating curl command: Call getHeaderLine()', $output);
    }

    public function testGetThrowableName(): void
    {
        $renderer = new HtmlRenderer();
        $name = $renderer->getThrowableName(new ErrorException());

        $this->assertSame('Error (' . ErrorException::class . ')', $name);
    }

    public function createServerInformationLinkDataProvider(): array
    {
        return [
            'not-exist' => [null, ''],
            'unknown' => ['unknown', ''],
            'apache' => ['apache', 'https://httpd.apache.org'],
            'nginx' => ['nginx', 'https://nginx.org'],
            'lighttpd' => ['lighttpd', 'https://lighttpd.net'],
            'iis-iis' => ['iis', 'https://iis.net'],
            'iis-services' => ['services', 'https://iis.net'],
            'development' => ['development', 'https://www.php.net/manual/en/features.commandline.webserver.php'],
        ];
    }

    /**
     * @dataProvider createServerInformationLinkDataProvider
     *
     * @param string|null $serverSoftware
     * @param string $expected
     */
    public function testCreateServerInformationLink(?string $serverSoftware, string $expected): void
    {
        $renderer = new HtmlRenderer();
        $serverRequestMock = $this->createServerRequestMock();
        $serverRequestMock->method('getServerParams')->willReturn(['SERVER_SOFTWARE' => $serverSoftware]);

        $this->assertStringContainsString($expected, $renderer->createServerInformationLink($serverRequestMock));
    }

    public function argumentsToStringValueDataProvider(): array
    {
        return [
            'int' => [[1], '1'],
            'float' => [[1.1], '1.1'],
            'bool' => [[true], 'true'],
            'null' => [[null], 'null'],
            'object' => [[new HtmlRenderer()], HtmlRenderer::class],
            'array' => [[['test-string-array']], 'test-string-array'],
            'resource' => [[fopen('php://memory', 'r')], 'resource'],
            'string-less-32' => [['test-string'], 'test-string'],
            'string-more-32' => [['qwertyuiopasdfghjklzxcvbnm1234567'], 'qwertyuiopasdfghjklzxcvbnm123456...'],
            'key-string' => [
                ['key' => 'value'],
                '<span class="string">\'key\'</span> => <span class="string">\'value\'</span>',
            ],
            'key-int' => [
                [111 => 'value'],
                '<span class="number">111</span> => <span class="string">\'value\'</span>',
            ],
        ];
    }

    /**
     * @dataProvider argumentsToStringValueDataProvider
     *
     * @param mixed $args
     * @param string $expected
     */
    public function testArgumentsToString(array $args, string $expected): void
    {
        $renderer = new HtmlRenderer();
        $value = $renderer->argumentsToString($args);

        $this->assertStringContainsString($expected, $value);
    }

    public function testGroupVendorCallStackItems(): void
    {
        $groupedItems = [
            2 => [
                2 => 'Item #2',
                3 => 'Item #3',
            ],
            5 => [
                5 => 'Item #5',
            ],
            16 => [
                16 => 'Item #16',
                17 => 'Item #17',
                18 => 'Item #18',
            ],
            54 => [
                54 => 'Item #54',
                55 => 'Item #55',
            ],
        ];

        $this->assertSame($groupedItems, $this->invokeMethod(new HtmlRenderer(), 'groupVendorCallStackItems', [
            'items' => [
                2 => 'Item #2',
                3 => 'Item #3',
                5 => 'Item #5',
                16 => 'Item #16',
                17 => 'Item #17',
                18 => 'Item #18',
                54 => 'Item #54',
                55 => 'Item #55',
            ],
        ]));
    }

    public function isVendorFileReturnFalseDataProvider(): array
    {
        return [
            'null' => [null],
            'not-exist' => ['not-exist'],
            'not-vendor-file' => [__FILE__],
        ];
    }

    /**
     * @dataProvider isVendorFileReturnFalseDataProvider
     *
     * @param string|null $file
     */
    public function testIsVendorFileReturnFalse(?string $file): void
    {
        $this->assertFalse($this->invokeMethod(new HtmlRenderer(), 'isVendorFile', ['file' => $file]));
    }

    public function testIsVendorFileWithPathsAlreadyAdded(): void
    {
        $renderer = new HtmlRenderer();

        $this->setVendorPaths($renderer, [__DIR__]);
        $this->assertTrue($this->invokeMethod($renderer, 'isVendorFile', ['file' => __FILE__]));

        $this->setVendorPaths($renderer, [dirname(__DIR__) . '/Middleware']);
        $this->assertFalse($this->invokeMethod($renderer, 'isVendorFile', ['file' => __FILE__]));
    }

    private function createServerRequestMock(): ServerRequestInterface
    {
        $serverRequestMock = $this->createMock(ServerRequestInterface::class);
        $acceptHeader = ['text/html'];
        $hostHeader = ['example.com'];

        $serverRequestMock
            ->method('getHeader')
            ->with('Accept')
            ->willReturn($acceptHeader);

        $serverRequestMock
            ->method('getHeader')
            ->with('Host')
            ->willReturn($hostHeader);

        $serverRequestMock
            ->method('getHeaders')
            ->willReturn(
                [
                    'Accept' => $acceptHeader,
                    'Host' => $hostHeader,
                ]
            );

        $serverRequestMock
            ->method('getMethod')
            ->willReturn('GET');

        $serverRequestMock
            ->method('getUri')
            ->willReturn(new Uri('https:/example.com'));

        $serverRequestMock
            ->method('getHeaderLine')
            ->willThrowException(new RuntimeException('Call getHeaderLine()'));

        return $serverRequestMock;
    }

    private function createTestTemplate(string $path, string $templateContents): void
    {
        if (!file_put_contents($path, $templateContents)) {
            throw new RuntimeException(sprintf('Unable to create file at path %s', $path));
        }
    }

    private function invokeMethod(object $object, string $method, array $args = [])
    {
        $reflection = new ReflectionObject($object);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        $result = $method->invokeArgs($object, $args);
        $method->setAccessible(false);
        return $result;
    }

    private function setVendorPaths(HtmlRenderer $renderer, array $vendorPaths): void
    {
        $reflection = new ReflectionClass($renderer);
        $property = $reflection->getProperty('vendorPaths');
        $property->setAccessible(true);
        $property->setValue($renderer, $vendorPaths);
        $property->setAccessible(false);
    }
}
