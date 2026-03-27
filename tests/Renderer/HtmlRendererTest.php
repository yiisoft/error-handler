<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Renderer;

use Exception;
use HttpSoft\Message\Uri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Closure;
use ReflectionClass;
use ReflectionObject;
use RuntimeException;
use Yiisoft\ErrorHandler\CompositeException;
use Yiisoft\ErrorHandler\Exception\ErrorException;
use Yiisoft\ErrorHandler\Renderer\HtmlRenderer;
use Yiisoft\ErrorHandler\Tests\Support\TestDocBlockException;
use Yiisoft\ErrorHandler\Tests\Support\TestEmptyDescriptionDocBlockException;
use Yiisoft\ErrorHandler\Tests\Support\TestExceptionWithoutDocBlock;
use Yiisoft\ErrorHandler\Tests\Support\TestHelper;
use Yiisoft\ErrorHandler\Tests\Support\TestInlineCodeDocBlockException;
use Yiisoft\ErrorHandler\Tests\Support\TestLeadingMarkdownLinkDocBlockException;
use Yiisoft\ErrorHandler\Tests\Support\NamespacedClosureTraceFixture;
use Yiisoft\ErrorHandler\Tests\Support\TestOwaspFilterEvasionDocBlockException;
use Yiisoft\ErrorHandler\Tests\Support\TestParenthesizedMarkdownDocBlockException;
use Yiisoft\ErrorHandler\Tests\Support\TestQueryStringDocBlockException;
use Yiisoft\ErrorHandler\Tests\Support\TestUnsafeDocBlockException;
use Yiisoft\ErrorHandler\Tests\Support\TestUnsafeMarkdownDocBlockException;
use stdClass;

use function dirname;
use function file_exists;
use function file_put_contents;
use function fopen;
use function Yiisoft\ErrorHandler\Tests\Support\loadFileLevelClosureException;
use function unlink;
use function sprintf;
use function count;

use const DIRECTORY_SEPARATOR;
use const PHP_VERSION_ID;

require_once dirname(__DIR__) . '/Support/FileLevelClosureLoader.php';

final class HtmlRendererTest extends TestCase
{
    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $template) {
            if (file_exists($template)) {
                unlink($template);
            }
        }

        $this->temporaryFiles = [];
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

    public function testVerboseOutputRendersThrowableDescriptionFromDocComment(): void
    {
        $renderer = new HtmlRenderer();
        $exception = new TestDocBlockException('exception-test-message');

        $errorData = $renderer->renderVerbose($exception, $this->createServerRequestMock());
        $result = (string) $errorData;

        $this->assertStringContainsString('<div class="exception-description">', $result);
        $this->assertStringContainsString('Test summary with <code>RuntimeException</code>.', $result);
        $this->assertStringContainsString(
            '<a href="https://www.yiiframework.com">Yii Framework</a>',
            $result,
        );
    }

    public function testVerboseOutputDoesNotRenderThrowableDescriptionWhenNoDocComment(): void
    {
        $renderer = new HtmlRenderer();
        $exception = new TestExceptionWithoutDocBlock('exception-test-message');

        $errorData = $renderer->renderVerbose($exception, $this->createServerRequestMock());

        $this->assertStringNotContainsString('<div class="exception-description">', (string) $errorData);
    }

    public function testVerboseOutputDoesNotRenderThrowableDescriptionWhenDocCommentHasNoDescription(): void
    {
        $renderer = new HtmlRenderer();
        $exception = new TestEmptyDescriptionDocBlockException('exception-test-message');

        $errorData = $renderer->renderVerbose($exception, $this->createServerRequestMock());

        $this->assertStringNotContainsString('<div class="exception-description">', (string) $errorData);
    }

    public function testVerboseOutputUsesFirstExceptionFromCompositeException(): void
    {
        $renderer = new HtmlRenderer();
        $first = new TestDocBlockException('first-message');
        $second = new RuntimeException('second-message');
        $exception = new CompositeException($first, $second);

        $errorData = $renderer->renderVerbose($exception, $this->createServerRequestMock());
        $result = (string) $errorData;

        $this->assertStringContainsString(TestDocBlockException::class, $result);
        $this->assertStringContainsString('first-message', $result);
        $this->assertStringContainsString('Test summary with <code>RuntimeException</code>.', $result);
    }

    public function testVerboseOutputEscapesUnsafeThrowableDescriptionLinks(): void
    {
        $renderer = new HtmlRenderer();
        $exception = new TestUnsafeDocBlockException('exception-test-message');

        $errorData = $renderer->renderVerbose($exception, $this->createServerRequestMock());
        $result = (string) $errorData;
        preg_match('/<div class="exception-description">(.*?)<\/div>/s', $result, $matches);
        $description = $matches[1] ?? '';

        $this->assertStringNotContainsString('href="javascript:alert(1)"', $result);
        $this->assertNotSame('', $description);
        $this->assertStringNotContainsString('<img', $description);
        $this->assertStringContainsString(
            '&lt;img src=&quot;x&quot; onerror=&quot;alert(1)&quot;&gt;',
            $description,
        );
        $this->assertStringContainsString('Click me (<code>javascript:alert(1)</code>)', $result);
        $this->assertStringContainsString(
            '<a href="https://www.yiiframework.com">Safe link</a>',
            $result,
        );
    }

    public function testVerboseOutputEscapesUnsafeThrowableDescriptionMarkdownPayloads(): void
    {
        $renderer = new HtmlRenderer();
        $exception = new TestUnsafeMarkdownDocBlockException('exception-test-message');

        $errorData = $renderer->renderVerbose($exception, $this->createServerRequestMock());
        $result = (string) $errorData;
        preg_match('/<div class="exception-description">(.*?)<\/div>/s', $result, $matches);
        $description = $matches[1] ?? '';

        $this->assertNotSame('', $description);
        $this->assertStringNotContainsString('href="javascript:alert(document.domain)"', $description);
        $this->assertStringNotContainsString('href="javascript:alert(\'html-link\')"', $description);
        $this->assertStringNotContainsString('<img', $description);
        $this->assertStringNotContainsString('<svg', $description);
        $this->assertStringContainsString('Click me (<code>javascript:alert(document.domain</code>))', $description);
        $this->assertStringContainsString('!Image payload (<code>javascript:alert(&#039;img&#039;</code>))', $description);
        $this->assertStringContainsString(
            '&lt;a href=&quot;javascript:alert(&#039;html-link&#039;)&quot;&gt;Raw HTML link&lt;/a&gt;',
            $description,
        );
        $this->assertStringContainsString(
            '&lt;svg onload=&quot;alert(&#039;svg&#039;)&quot;&gt;&lt;/svg&gt;',
            $description,
        );
    }

    public function testVerboseOutputEscapesNonHttpSchemesInThrowableDescriptionMarkdownLinks(): void
    {
        $renderer = new HtmlRenderer();
        $exception = new TestUnsafeMarkdownDocBlockException('exception-test-message');

        $errorData = $renderer->renderVerbose($exception, $this->createServerRequestMock());
        $result = (string) $errorData;
        preg_match('/<div class="exception-description">(.*?)<\/div>/s', $result, $matches);
        $description = $matches[1] ?? '';

        $this->assertNotSame('', $description);
        $this->assertStringContainsString('Encoded payload (<code>JaVaScRiPt:alert(1</code>))', $description);
        $this->assertStringContainsString(
            'Data URL (<code>data:text/html,&lt;script&gt;alert(1</code>)&lt;/script&gt;)',
            $description,
        );
        $this->assertStringContainsString(
            '<a href="https://www.yiiframework.com">Safe link</a>',
            $description,
        );
    }

    public function testVerboseOutputEscapesOwaspFilterEvasionThrowableDescriptionPayloads(): void
    {
        $renderer = new HtmlRenderer();
        $exception = new TestOwaspFilterEvasionDocBlockException('exception-test-message');

        $errorData = $renderer->renderVerbose($exception, $this->createServerRequestMock());
        $result = (string) $errorData;
        preg_match('/<div class="exception-description">(.*?)<\/div>/s', $result, $matches);
        $description = $matches[1] ?? '';

        $this->assertNotSame('', $description);
        $this->assertStringNotContainsString('<a href=', $description);
        $this->assertStringNotContainsString('<img', $description);
        $this->assertStringContainsString(
            '&lt;a href=&quot;&amp;#0000106&amp;#0000097&amp;#0000118',
            $description,
        );
        $this->assertStringContainsString(
            '&lt;a href=&quot;jav&amp;#x09;ascript:alert(&#039;XSS&#039;);&quot;&gt;Encoded tab payload&lt;/a&gt;',
            $description,
        );
        $this->assertStringContainsString(
            '&lt;img src= onmouseover=&quot;alert(&#039;xss&#039;)&quot;&gt;',
            $description,
        );
        $this->assertStringContainsString(
            '&lt;img onmouseover=&quot;alert(&#039;xss&#039;)&quot;&gt;',
            $description,
        );
        $this->assertStringContainsString(
            '&lt;img dynsrc=&quot;javascript:alert(&#039;XSS&#039;)&quot;&gt;',
            $description,
        );
        $this->assertStringContainsString(
            '&lt;img lowsrc=&quot;javascript:alert(&#039;XSS&#039;)&quot;&gt;',
            $description,
        );
    }

    public function testVerboseOutputRendersInlineCodeAndSeeTagWithoutLabel(): void
    {
        $renderer = new HtmlRenderer();
        $exception = new TestInlineCodeDocBlockException('exception-test-message');

        $errorData = $renderer->renderVerbose($exception, $this->createServerRequestMock());
        $result = (string) $errorData;

        $this->assertStringContainsString('<code>inline-code</code>', $result);
        $this->assertStringContainsString('<code>RuntimeException</code>', $result);
    }

    public function testVerboseOutputDoesNotDoubleEncodeSafeThrowableDescriptionLinks(): void
    {
        $renderer = new HtmlRenderer();
        $exception = new TestQueryStringDocBlockException('exception-test-message');

        $errorData = $renderer->renderVerbose($exception, $this->createServerRequestMock());
        $result = (string) $errorData;
        preg_match('/<div class="exception-description">(.*?)<\/div>/s', $result, $matches);
        $description = $matches[1] ?? '';

        $this->assertStringContainsString(
            '<a href="https://www.yiiframework.com/search?q=error&amp;lang=en">Yii Search</a>',
            $description,
        );
        $this->assertStringNotContainsString(
            'https://www.yiiframework.com/search?q=error&amp;amp;lang=en',
            $description,
        );
    }

    public function testVerboseOutputRendersThrowableDescriptionStartingWithMarkdownLink(): void
    {
        $renderer = new HtmlRenderer();
        $exception = new TestLeadingMarkdownLinkDocBlockException('exception-test-message');

        $errorData = $renderer->renderVerbose($exception, $this->createServerRequestMock());
        $result = (string) $errorData;
        preg_match('/<div class="exception-description">(.*?)<\/div>/s', $result, $matches);
        $description = $matches[1] ?? '';

        $this->assertNotSame('', $description);
        $this->assertStringContainsString(
            '<a href="https://www.yiiframework.com">Yii Framework</a> starts this description.',
            $description,
        );
    }

    public function testVerboseOutputRendersThrowableDescriptionLinksWithParentheses(): void
    {
        $renderer = new HtmlRenderer();
        $exception = new TestParenthesizedMarkdownDocBlockException('exception-test-message');

        $errorData = $renderer->renderVerbose($exception, $this->createServerRequestMock());
        $result = (string) $errorData;
        preg_match('/<div class="exception-description">(.*?)<\/div>/s', $result, $matches);
        $description = $matches[1] ?? '';

        $this->assertNotSame('', $description);
        $this->assertStringContainsString(
            '<a href="https://en.wikipedia.org/wiki/Function_(mathematics)">Wiki</a>',
            $description,
        );
        $this->assertStringContainsString(
            '<a href="https://en.wikipedia.org/wiki/Function_(mathematics)">Inline wiki</a>',
            $description,
        );
    }

    public function testNonVerboseOutputWithCustomTemplate(): void
    {
        $settings = $this->createCustomSetting();
        $templateFileContents = '<html><?php echo $throwable->getMessage();?></html>';
        $this->createTestTemplate($settings['template'], $templateFileContents);

        $renderer = new HtmlRenderer($settings);
        $exceptionMessage = 'exception-test-message';
        $exception = new RuntimeException($exceptionMessage);

        $errorData = $renderer->render($exception, $this->createServerRequestMock());
        $this->assertStringContainsString("<html>$exceptionMessage</html>", (string) $errorData);
    }

    public function testVerboseOutputWithCustomTemplate(): void
    {
        $settings = $this->createCustomSetting();
        $templateFileContents = '<html><?php echo $throwable->getMessage();?></html>';
        $this->createTestTemplate($settings['verboseTemplate'], $templateFileContents);

        $renderer = new HtmlRenderer($settings);
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
        $settings = $this->createCustomSetting();
        $this->createTestTemplate($settings['verboseTemplate'], '<html><?php throw $throwable;?></html>');
        $renderer = new HtmlRenderer($settings);
        $exceptionMessage = 'Template error.';
        $exception = new RuntimeException($exceptionMessage);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($exceptionMessage);
        $renderer->renderVerbose($exception, $this->createServerRequestMock());
    }

    public function testRenderPreviousExceptions(): void
    {
        $settings = $this->createCustomSetting();
        $previousExceptionMessage = 'Test Previous Exception.';
        $exception = new RuntimeException('Some error.', 0, new Exception($previousExceptionMessage));
        $templateFileContents = '<?php echo $this->renderPreviousExceptions($throwable); ?>';
        $this->createTestTemplate($settings['verboseTemplate'], $templateFileContents);
        $renderer = new HtmlRenderer($settings);

        $errorData = $renderer->renderVerbose($exception, $this->createServerRequestMock());
        $this->assertStringContainsString($previousExceptionMessage, (string) $errorData);
    }

    public function testRenderCallStack(): void
    {
        $renderer = new HtmlRenderer();
        $this->setVendorPaths($renderer, [dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'vendor']);

        $this->assertStringContainsString(
            'new RuntimeException(&#039;Some error.&#039;)',
            $renderer->renderCallStack(new RuntimeException('Some error.')),
        );
    }

    #[WithoutErrorHandler]
    public function testRenderCallStackItemIfFileIsNotExistAndLineMoreZero(): void
    {
        $errorMessage = null;
        set_error_handler(
            static function (int $code, string $message) use (&$errorMessage) {
                $errorMessage = $message;
            },
        );
        $result = $this->invokeMethod(new HtmlRenderer(), 'renderCallStackItem', [
            'file' => 'not-exist',
            'line' => 1,
            'class' => null,
            'function' => null,
            'args' => [],
            'index' => 1,
            'isVendorFile' => false,
            'reflectionParameters' => [],
        ]);
        restore_error_handler();

        $this->assertStringContainsString('not-exist', $result);
        $this->assertStringContainsString('call-stack-item', $result);
        $this->assertSame('file(not-exist): Failed to open stream: No such file or directory', $errorMessage);
    }

    public function testRenderCallStackWithErrorException(): void
    {
        $renderer = new HtmlRenderer();

        $result = $renderer->renderCallStack(
            new ErrorException('test-message'),
            TestHelper::generateTrace([true, true, false, true]),
        );

        $this->assertStringContainsString('1. ', $result);
        $this->assertStringContainsString('2. ', $result);
        $this->assertStringContainsString('3. ', $result);
        $this->assertStringContainsString('4. ', $result);
        $this->assertStringContainsString('5. ', $result);
    }

    public function testRenderCallStackItemDoesNotRenderSourceCodeWhenLineIsOutsideFileRange(): void
    {
        $line = count(file(__FILE__)) + 1;
        $result = $this->invokeMethod(new HtmlRenderer(), 'renderCallStackItem', [
            'file' => __FILE__,
            'line' => $line,
            'class' => null,
            'function' => null,
            'args' => [],
            'index' => 1,
            'isVendorFile' => false,
            'reflectionParameters' => [],
        ]);

        $this->assertStringContainsString(__FILE__, $result);
        $this->assertStringContainsString('at line ' . $line, $result);
        $this->assertStringNotContainsString('element-code-wrap', $result);
    }

    public function testRenderRequest(): void
    {
        $renderer = new HtmlRenderer();
        $output = $renderer->renderRequest($this->createServerRequestMock());

        $this->assertSame(
            "GET https:/example.com\nAccept: text/html\n",
            $output,
        );
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

    public static function createServerInformationLinkDataProvider(): array
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

    #[DataProvider('createServerInformationLinkDataProvider')]
    public function testCreateServerInformationLink(?string $serverSoftware, string $expected): void
    {
        $renderer = new HtmlRenderer();
        $serverRequestMock = $this->createServerRequestMock();
        $serverRequestMock
            ->method('getServerParams')
            ->willReturn(['SERVER_SOFTWARE' => $serverSoftware]);

        $this->assertStringContainsString($expected, $renderer->createServerInformationLink($serverRequestMock));
    }

    public static function argumentsToStringValueDataProvider(): array
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

    #[DataProvider('argumentsToStringValueDataProvider')]
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

    public static function isVendorFileReturnFalseDataProvider(): array
    {
        return [
            'null' => [null],
            'not-exist' => ['not-exist'],
            'not-vendor-file' => [__FILE__],
        ];
    }

    #[DataProvider('isVendorFileReturnFalseDataProvider')]
    public function testIsVendorFileReturnFalse(?string $file): void
    {
        $this->assertFalse($this->invokeMethod(new HtmlRenderer(), 'isVendorFile', ['file' => $file]));
    }

    public function testIsVendorFileWithPathsAlreadyAdded(): void
    {
        $renderer = new HtmlRenderer();

        $this->setVendorPaths($renderer, [__DIR__]);
        $this->assertTrue($this->invokeMethod($renderer, 'isVendorFile', ['file' => __FILE__]));

        $this->setVendorPaths($renderer, [dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Middleware']);
        $this->assertFalse($this->invokeMethod($renderer, 'isVendorFile', ['file' => __FILE__]));
    }

    public static function dataTraceLinkGenerator(): iterable
    {
        yield [null, static fn() => null];
        yield [
            'phpstorm://open?file=test.php&line=42',
            static fn(string $file, ?int $line) => "phpstorm://open?file=$file&line=$line",
        ];
        yield [
            'phpstorm://open?file=test.php&line=42',
            'phpstorm://open?file={file}&line={line}',
        ];
        yield [
            'phpstorm://open?file=test.php&line=',
            'phpstorm://open?file={file}&line={line}',
            'test.php',
            null,
        ];
    }

    #[DataProvider('dataTraceLinkGenerator')]
    public function testTraceLinkGenerator(
        ?string $expected,
        mixed $traceLink,
        string $file = 'test.php',
        ?int $line = 42,
    ): void {
        $renderer = new HtmlRenderer(traceLink: $traceLink);

        $link = ($renderer->traceLinkGenerator)($file, $line);

        $this->assertSame($expected, $link);
    }

    public function testRenderCallStackWithMethodClosure(): void
    {
        $renderer = new HtmlRenderer();
        $exception = $this->createMethodClosureException();
        $traceItem = $exception->getTrace()[0];

        $this->assertArrayHasKey('file', $traceItem);
        $this->assertArrayHasKey('line', $traceItem);
        $this->assertSame(self::class, $traceItem['class']);
        $this->assertStringContainsString('{closure', $traceItem['function']);

        $result = $renderer->renderCallStack($exception, $exception->getTrace());

        $this->assertStringContainsString('{closure}', $result);
        $this->assertStringNotContainsString(self::class . '::' . $traceItem['function'], $result);

        if (PHP_VERSION_ID >= 80400) {
            $this->assertMatchesRegularExpression(
                '/\{closure\}\s+' . preg_quote(self::class, '/') . '::createMethodClosureException\(\):\d+/',
                $result,
            );
            return;
        }

        $this->assertStringContainsString(self::class . '::{closure}', $result);
    }

    public function testRenderCallStackWithBoundClosure(): void
    {
        $renderer = new HtmlRenderer();
        $exception = $this->createBoundClosureException();
        $traceItem = $exception->getTrace()[0];

        $this->assertSame('Closure', $traceItem['class']);
        $this->assertStringContainsString('{closure', $traceItem['function']);

        $result = $renderer->renderCallStack($exception, $exception->getTrace());
        $itemResult = $this->invokeMethod($renderer, 'renderCallStackItem', [
            $traceItem['file'] ?? null,
            $traceItem['line'] ?? null,
            $traceItem['class'] ?? null,
            $traceItem['function'] ?? null,
            $traceItem['args'] ?? [],
            2,
            false,
            [],
        ]);

        $this->assertStringContainsString('{closure}', $result);
        $this->assertStringContainsString('{closure}', $itemResult);
        $this->assertStringNotContainsString('Closure::{closure}', $itemResult);

        if (PHP_VERSION_ID >= 80400) {
            $this->assertMatchesRegularExpression('/\{closure\}\s+.+::createBoundClosureException\(\):\d+/', $result);
            return;
        }

        $this->assertStringContainsString('Yiisoft\\ErrorHandler\\Tests\\Renderer\\{closure}', $itemResult);
    }

    public function testRenderCallStackWithInternalFunctionClosure(): void
    {
        $renderer = new HtmlRenderer();
        $exception = $this->createInternalFunctionClosureException();
        $traceItem = $exception->getTrace()[0];

        $this->assertArrayNotHasKey('file', $traceItem);
        $this->assertArrayNotHasKey('line', $traceItem);
        $this->assertSame(self::class, $traceItem['class']);
        $this->assertStringContainsString('{closure', $traceItem['function']);

        $result = $renderer->renderCallStack($exception, $exception->getTrace());
        $itemResult = $this->invokeMethod($renderer, 'renderCallStackItem', [
            $traceItem['file'] ?? null,
            $traceItem['line'] ?? null,
            $traceItem['class'] ?? null,
            $traceItem['function'] ?? null,
            $traceItem['args'] ?? [],
            2,
            false,
            [],
        ]);

        $this->assertStringContainsString('{closure}', $result);
        $this->assertStringNotContainsString(self::class . '::' . $traceItem['function'], $result);
        $this->assertStringContainsString('{closure}', $itemResult);
        $this->assertStringNotContainsString('element-code-wrap', $itemResult);

        if (PHP_VERSION_ID >= 80400) {
            $this->assertMatchesRegularExpression(
                '/\{closure\}\s+' . preg_quote(self::class, '/') . '::createInternalFunctionClosureException\(\):\d+/',
                $result,
            );
            return;
        }

        $this->assertStringContainsString(self::class . '::{closure}', $result);
    }

    public function testRenderCallStackWithNamespacedClosureOnPhpBelow84(): void
    {
        if (PHP_VERSION_ID >= 80400) {
            $this->markTestSkipped('PHP < 8.4 specific behavior.');
        }

        $renderer = new HtmlRenderer();
        $exception = NamespacedClosureTraceFixture::createException();
        $traceItem = $exception->getTrace()[0];

        $this->assertSame(NamespacedClosureTraceFixture::class, $traceItem['class']);
        $this->assertSame('Yiisoft\\ErrorHandler\\Tests\\Support\\{closure}', $traceItem['function']);

        $result = $renderer->renderCallStack($exception, $exception->getTrace());

        $this->assertStringContainsString(NamespacedClosureTraceFixture::class . '::{closure}', $result);
        $this->assertStringNotContainsString(
            NamespacedClosureTraceFixture::class . '::' . $traceItem['function'],
            $result,
        );
    }

    public function testRenderCallStackWithFileLevelClosureOnPhp84Plus(): void
    {
        if (PHP_VERSION_ID < 80400) {
            $this->markTestSkipped('PHP 8.4+ specific behavior.');
        }

        $renderer = new HtmlRenderer();
        $exception = loadFileLevelClosureException();
        $traceItem = $exception->getTrace()[0];

        $this->assertArrayNotHasKey('class', $traceItem);
        $this->assertStringContainsString('{closure:', $traceItem['function']);

        $result = $renderer->renderCallStack($exception, $exception->getTrace());

        $this->assertMatchesRegularExpression(
            '#\{closure\}\s+.+/tests/Support/file_level_closure_exception\.php:\d+#',
            $result,
        );
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
                ],
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

    private function createMethodClosureException(): RuntimeException
    {
        $closure = function (): void {
            throw new RuntimeException('test');
        };

        try {
            $closure();
        } catch (RuntimeException $e) {
            return $e;
        }

        $this->fail('Expected exception from method closure.');
        throw new RuntimeException('Unreachable.');
    }

    private function createInternalFunctionClosureException(): RuntimeException
    {
        $closure = function (int $value): void {
            throw new RuntimeException((string) $value);
        };

        try {
            array_map($closure, [1]);
        } catch (RuntimeException $e) {
            return $e;
        }

        $this->fail('Expected exception from closure called via internal function.');
        throw new RuntimeException('Unreachable.');
    }

    private function createBoundClosureException(): RuntimeException
    {
        $closure = function (): void {
            throw new RuntimeException('test');
        };

        $boundClosure = Closure::bind($closure, new stdClass(), null);
        $this->assertInstanceOf(Closure::class, $boundClosure);

        try {
            $boundClosure();
        } catch (RuntimeException $e) {
            return $e;
        }

        $this->fail('Expected exception from Closure scope closure.');
        throw new RuntimeException('Unreachable.');
    }

    private function createTestTemplate(string $path, string $templateContents): void
    {
        if (!file_put_contents($path, $templateContents)) {
            throw new RuntimeException(sprintf('Unable to create file at path %s', $path));
        }
    }

    private function createCustomSetting(): array
    {
        $verboseTemplate = tempnam(sys_get_temp_dir(), 'verbose-template-');
        $template = tempnam(sys_get_temp_dir(), 'template-');

        if ($verboseTemplate === false || $template === false) {
            throw new RuntimeException('Unable to create temporary template paths.');
        }

        $this->temporaryFiles[] = $verboseTemplate;
        $this->temporaryFiles[] = $template;

        return [
            'verboseTemplate' => $verboseTemplate,
            'template' => $template,
        ];
    }

    private function invokeMethod(object $object, string $method, array $args = [])
    {
        $reflection = new ReflectionObject($object);
        $method = $reflection->getMethod($method);
        return $method->invokeArgs($object, $args);
    }

    private function setVendorPaths(HtmlRenderer $renderer, array $vendorPaths): void
    {
        $reflection = new ReflectionClass($renderer);
        $property = $reflection->getProperty('vendorPaths');
        $property->setValue($renderer, $vendorPaths);
    }
}
