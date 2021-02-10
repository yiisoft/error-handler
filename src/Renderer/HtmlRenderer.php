<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Renderer;

use Alexkart\CurlBuilder\Command;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Throwable;
use Yiisoft\ErrorHandler\ThrowableRenderer;

use function array_values;
use function dirname;
use function extract;
use function file;
use function file_exists;
use function func_get_arg;
use function get_class;
use function htmlspecialchars;
use function implode;
use function is_array;
use function is_bool;
use function is_object;
use function is_resource;
use function is_string;
use function ksort;
use function mb_strlen;
use function mb_substr;
use function ob_clean;
use function ob_get_clean;
use function ob_get_level;
use function ob_end_clean;
use function ob_implicit_flush;
use function ob_start;
use function realpath;
use function rtrim;
use function stripos;
use function strpos;

/**
 * Formats exception into HTML string.
 */
final class HtmlRenderer extends ThrowableRenderer
{
    /**
     * @var string The full path to the default template directory.
     */
    private string $defaultTemplatePath;

    /**
     * @var string The full path of the template file for rendering exceptions without call stack information.
     *
     * This template should be used in production.
     */
    private string $template;

    /**
     * @var string The full path of the template file for rendering exceptions with call stack information.
     *
     * This template should be used in development.
     */
    private string $verboseTemplate;

    /**
     * @var int The maximum number of source code lines to be displayed. Defaults to 19.
     */
    private int $maxSourceLines;

    /**
     * @var int The maximum number of trace source code lines to be displayed. Defaults to 13.
     */
    private int $maxTraceLines;

    /**
     * @var string|null The trace header line with placeholders to be be substituted. Defaults to null.
     *
     * The placeholders are {file}, {line} and {ide}. A typical use case is the creation of IDE-specific links,
     * since when you click on a trace header link, it opens directly in the IDE. You can also insert custom content.
     *
     * Example IDE link:
     *
     * ```
     * <a href="ide://open?file={file}&line={line}">{ide}</a>
     * ```
     */
    private ?string $traceHeaderLine;

    /**
     * @param array $settings Settings can have the following keys:
     * - template: string, full path of the template file for rendering exceptions without call stack information.
     * - verboseTemplate: string, full path of the template file for rendering exceptions with call stack information.
     * - maxSourceLines: int, maximum number of source code lines to be displayed. Defaults to 19.
     * - maxTraceLines: int, maximum number of trace source code lines to be displayed. Defaults to 13.
     * - traceHeaderLine: string, trace header line with placeholders to be be substituted. Defaults to null.
     */
    public function __construct(array $settings = [])
    {
        $this->defaultTemplatePath = dirname(__DIR__, 2) . '/templates';
        $this->template = $settings['template'] ?? $this->defaultTemplatePath . '/production.php';
        $this->verboseTemplate = $settings['verboseTemplate'] ?? $this->defaultTemplatePath . '/development.php';
        $this->maxSourceLines = $settings['maxSourceLines']  ?? 19;
        $this->maxTraceLines = $settings['maxTraceLines']  ?? 13;
        $this->traceHeaderLine = $settings['traceHeaderLine'] ?? null;
    }

    public function render(Throwable $t, ServerRequestInterface $request = null): string
    {
        return $this->renderTemplate($this->template, [
            'request' => $request,
            'throwable' => $t,
        ]);
    }

    public function renderVerbose(Throwable $t, ServerRequestInterface $request = null): string
    {
        return $this->renderTemplate($this->verboseTemplate, [
            'request' => $request,
            'throwable' => $t,
        ]);
    }

    /**
     * Encodes special characters into HTML entities for use as a content.
     *
     * @param string $content The content to be encoded.
     *
     * @return string Encoded content.
     */
    private function htmlEncode(string $content): string
    {
        return htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Renders a template.
     *
     * @param string $path The full path of the template file for rendering.
     * @param array $parameters The name-value pairs that will be extracted and made available in the template file.
     *
     * @throws Throwable
     *
     * @return string The rendering result.
     */
    private function renderTemplate(string $path, array $parameters): string
    {
        if (!file_exists($path)) {
            throw new RuntimeException("Template not found at $path");
        }

        $renderer = function (): void {
            extract(func_get_arg(1), EXTR_OVERWRITE);
            require func_get_arg(0);
        };

        $obInitialLevel = ob_get_level();
        ob_start();
        PHP_VERSION_ID >= 80000 ? ob_implicit_flush(false) : ob_implicit_flush(0);

        try {
            $renderer->bindTo($this)($path, $parameters);
            return ob_get_clean();
        } catch (Throwable $e) {
            while (ob_get_level() > $obInitialLevel) {
                if (!@ob_end_clean()) {
                    ob_clean();
                }
            }
            throw $e;
        }
    }

    /**
     * Renders the previous exception stack for a given Exception.
     *
     * @param Throwable $t The exception whose precursors should be rendered.
     *
     * @throws Throwable
     *
     * @return string HTML content of the rendered previous exceptions. Empty string if there are none.
     */
    private function renderPreviousExceptions(Throwable $t): string
    {
        if (($previous = $t->getPrevious()) !== null) {
            $templatePath = $this->defaultTemplatePath . '/_previous-exception.php';
            return $this->renderTemplate($templatePath, ['throwable' => $previous]);
        }

        return '';
    }

    /**
     * Renders call stack.
     *
     * @param Throwable $t The exception to get call stack from.
     *
     * @throws Throwable
     *
     * @return string HTML content of the rendered call stack.
     */
    private function renderCallStack(Throwable $t): string
    {
        $out = '<ul>';
        $out .= $this->renderCallStackItem($t->getFile(), $t->getLine(), null, null, [], 1);

        for ($i = 0, $trace = $t->getTrace(), $length = count($trace); $i < $length; ++$i) {
            $file = !empty($trace[$i]['file']) ? $trace[$i]['file'] : null;
            $line = !empty($trace[$i]['line']) ? $trace[$i]['line'] : null;
            $class = !empty($trace[$i]['class']) ? $trace[$i]['class'] : null;
            $function = null;
            if (!empty($trace[$i]['function']) && $trace[$i]['function'] !== 'unknown') {
                $function = $trace[$i]['function'];
            }
            $args = !empty($trace[$i]['args']) ? $trace[$i]['args'] : [];
            $out .= $this->renderCallStackItem($file, $line, $class, $function, $args, $i + 2);
        }

        $out .= '</ul>';
        return $out;
    }

    /**
     * Renders a single call stack element.
     *
     * @param string|null $file The name where call has happened.
     * @param int|null $line The number on which call has happened.
     * @param string|null $class The called class name.
     * @param string|null $function The called function/method name.
     * @param array $args The array of method arguments.
     * @param int $index The number of the call stack element.
     *
     * @throws Throwable
     *
     * @return string HTML content of the rendered call stack element.
     */
    private function renderCallStackItem(?string $file, ?int $line, ?string $class, ?string $function, array $args, int $index): string
    {
        $lines = [];
        $begin = $end = 0;

        if ($file !== null && $line !== null) {
            $line--; // adjust line number from one-based to zero-based
            $lines = @file($file);
            if ($line < 0 || $lines === false || ($lineCount = count($lines)) < $line) {
                return '';
            }
            $half = (int) (($index === 1 ? $this->maxSourceLines : $this->maxTraceLines) / 2);
            $begin = $line - $half > 0 ? $line - $half : 0;
            $end = $line + $half < $lineCount ? $line + $half : $lineCount - 1;
        }

        return $this->renderTemplate($this->defaultTemplatePath . '/_call-stack-item.php', [
            'file' => $file,
            'line' => $line,
            'class' => $class,
            'function' => $function,
            'index' => $index,
            'lines' => $lines,
            'begin' => $begin,
            'end' => $end,
            'args' => $args,
        ]);
    }

    /**
     * Converts arguments array to its string representation.
     *
     * @param array $args arguments array to be converted
     *
     * @return string The string representation of the arguments array.
     */
    private function argumentsToString(array $args): string
    {
        $count = 0;
        $isAssoc = $args !== array_values($args);

        foreach ($args as $key => $value) {
            $count++;

            if ($count >= 5) {
                if ($count > 5) {
                    unset($args[$key]);
                } else {
                    $args[$key] = '...';
                }
                continue;
            }

            if (is_object($value)) {
                $args[$key] = '<span class="title">' . $this->htmlEncode(get_class($value)) . '</span>';
            } elseif (is_bool($value)) {
                $args[$key] = '<span class="keyword">' . ($value ? 'true' : 'false') . '</span>';
            } elseif (is_string($value)) {
                $fullValue = $this->htmlEncode($value);
                if (mb_strlen($value, 'UTF-8') > 32) {
                    $displayValue = $this->htmlEncode(mb_substr($value, 0, 32, 'UTF-8')) . '...';
                    $args[$key] = "<span class=\"string\" title=\"$fullValue\">'$displayValue'</span>";
                } else {
                    $args[$key] = "<span class=\"string\">'$fullValue'</span>";
                }
            } elseif (is_array($value)) {
                unset($args[$key]);
                $args[$key] = '[' . $this->argumentsToString($value) . ']';
            } elseif ($value === null) {
                $args[$key] = '<span class="keyword">null</span>';
            } elseif (is_resource($value)) {
                $args[$key] = '<span class="keyword">resource</span>';
            } else {
                $args[$key] = '<span class="number">' . $value . '</span>';
            }

            if (is_string($key)) {
                $args[$key] = '<span class="string">\'' . $this->htmlEncode($key) . "'</span> => $args[$key]";
            } elseif ($isAssoc) {
                $args[$key] = "<span class=\"number\">$key</span> => $args[$key]";
            }
        }

        ksort($args);
        return implode(', ', $args);
    }

    /**
     * Renders the information about request.
     *
     * @param ServerRequestInterface $request
     *
     * @return string The rendering result.
     */
    private function renderRequest(ServerRequestInterface $request): string
    {
        $output = $request->getMethod() . ' ' . $request->getUri() . "\n";

        foreach ($request->getHeaders() as $name => $values) {
            if ($name === 'Host') {
                continue;
            }

            foreach ($values as $value) {
                $output .= "$name: $value\n";
            }
        }

        $output .= "\n" . $request->getBody() . "\n\n";

        return '<pre>' . $this->htmlEncode(rtrim($output, "\n")) . '</pre>';
    }

    /**
     * Renders the information about curl request.
     *
     * @param ServerRequestInterface $request
     *
     * @return string The rendering result.
     */
    private function renderCurl(ServerRequestInterface $request): string
    {
        try {
            $output = (new Command())->setRequest($request)->build();
        } catch (Throwable $e) {
            $output = 'Error generating curl command: ' . $e->getMessage();
        }

        return $this->htmlEncode($output);
    }

    /**
     * Creates string containing HTML link which refers to the home page
     * of determined web-server software and its full name.
     *
     * @param ServerRequestInterface $request
     *
     * @return string The server software information hyperlink.
     */
    private function createServerInformationLink(ServerRequestInterface $request): string
    {
        $serverSoftware = $request->getServerParams()['SERVER_SOFTWARE'] ?? null;

        if ($serverSoftware === null) {
            return '';
        }

        $serverUrls = [
            'https://httpd.apache.org/' => ['apache'],
            'https://nginx.org/' => ['nginx'],
            'https://lighttpd.net/' => ['lighttpd'],
            'https://iis.net/' => ['iis', 'services'],
            'https://www.php.net/manual/en/features.commandline.webserver.php' => ['development'],
        ];

        foreach ($serverUrls as $url => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($serverSoftware, $keyword) !== false) {
                    return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">'
                        . $this->htmlEncode($serverSoftware) . '</a>';
                }
            }
        }

        return '';
    }

    /**
     * Determines whether given name of the file belongs to the framework.
     *
     * @param string|null $file The name to be checked.
     *
     * @return bool Whether given name of the file belongs to the framework.
     */
    public function isCoreFile(?string $file): bool
    {
        return $file === null || strpos(realpath($file), dirname(__DIR__, 3)) === 0;
    }
}
