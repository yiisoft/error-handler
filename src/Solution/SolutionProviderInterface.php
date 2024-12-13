<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Solution;

interface SolutionProviderInterface
{
    public function supports(\Throwable $e): bool;

    public function generate(\Throwable $e): string;
}
