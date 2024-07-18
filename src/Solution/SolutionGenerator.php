<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Solution;

final class SolutionGenerator
{
    public function __construct(
        /**
         * @var SolutionGeneratorInterface[]
         */
        private array $generators,
    ) {
    }

    /**
     * @return string[]
     */
    public function generate(\Throwable $e): array
    {
        $result = [];

        foreach ($this->generators as $generator) {
            if ($generator->supports($e)) {
                $result[] = $generator->generate($e);
            }
        }
        return $result;
    }
}
