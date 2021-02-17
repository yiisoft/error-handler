<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Renderer;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Yiisoft\ErrorHandler\ErrorData;
use Yiisoft\ErrorHandler\ThrowableRenderer;

/**
 * Formats exception into XML string.
 */
final class XmlRenderer extends ThrowableRenderer
{
    public function render(Throwable $t, ServerRequestInterface $request = null): ErrorData
    {
        $content = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
        $content .= "<error>\n";
        $content .= $this->tag('message', 'An internal server error occurred');
        $content .= '</error>';
        return new ErrorData($content);
    }

    public function renderVerbose(Throwable $t, ServerRequestInterface $request = null): ErrorData
    {
        $content = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
        $content .= "<error>\n";
        $content .= $this->tag('type', get_class($t));
        $content .= $this->tag('message', $this->cdata($t->getMessage()));
        $content .= $this->tag('code', $this->cdata((string) $t->getCode()));
        $content .= $this->tag('file', $t->getFile());
        $content .= $this->tag('line', (string) $t->getLine());
        $content .= $this->tag('trace', $t->getTraceAsString());
        $content .= '</error>';
        return new ErrorData($content);
    }

    private function tag(string $name, string $value): string
    {
        return "<$name>" . $value . "</$name>\n";
    }

    private function cdata(string $value): string
    {
        return '<![CDATA[' . str_replace(']]>', ']]]]><![CDATA[>', $value) . ']]>';
    }
}
