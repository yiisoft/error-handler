<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Yiisoft\ErrorHandler\ThrowableRenderer;

final class MockThrowableRenderer extends ThrowableRenderer
{
    private string $response;

    public function __construct(string $response)
    {
        $this->response = $response;
    }

    public function render(Throwable $t, ServerRequestInterface $request = null): string
    {
        return $this->response;
    }

    public function renderVerbose(Throwable $t, ServerRequestInterface $request = null): string
    {
        return $this->response;
    }
}
