<?php

declare(strict_types=1);


/**
 * @var array $params
 */

return [
    'yiisoft/error-handler' => [
        'solutionProviders' => [
            \Yiisoft\Definitions\Reference::to(\Yiisoft\ErrorHandler\Solution\FriendlyExceptionSolution::class),
        ],
    ],
];
