<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Solution;

/**
* The interface declares an adapter to render a solution for an event.
* Basically, it renders the error message as-is, but possible could render a button with click-to-fix action that will be handled by an HTTP request (with middleware) back to server.
*/
interface SolutionProviderInterface
{
    public function supports(\Throwable $e): bool;

    public function generate(\Throwable $e): string;
}
