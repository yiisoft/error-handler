<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Renderer;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Yiisoft\ErrorHandler\ThrowableRenderer;

/**
 * Formats exception into XML string.
 */
final class XmlRenderer extends ThrowableRenderer
{
    public function render(Throwable $t, ServerRequestInterface $request = null): string
    {
        $out = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
        $out .= "<error>\n";
        $out .= $this->tag('message', 'An internal server error occurred');
        $out .= '</error>';
        return $out;
    }

    public function renderVerbose(Throwable $t, ServerRequestInterface $request = null): string
    {
        $out = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
        $out .= "<error>\n";
        $out .= $this->tag('type', get_class($t));
        $out .= $this->tag('message', $this->cdata($t->getMessage()));
        $out .= $this->tag('code', $this->cdata((string) $t->getCode()));
        $out .= $this->tag('file', $t->getFile());
        $out .= $this->tag('line', (string) $t->getLine());
        $out .= $this->tag('trace', $t->getTraceAsString());
        $out .= '</error>';
        return $out;
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
