<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Solution;

use Yiisoft\FriendlyException\FriendlyExceptionInterface;

final class FriendlyExceptionSolution implements SolutionProviderInterface
{
    public function supports(\Throwable $e): bool
    {
        return $e instanceof FriendlyExceptionInterface && $e->getSolution() !== null;
    }

    public function generate(\Throwable $e): string
    {
        return $e->getSolution();
    }
}
