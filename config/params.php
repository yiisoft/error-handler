<?php

declare(strict_types=1);

use Yiisoft\ErrorHandler\Renderer\HtmlRenderer;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;

/**
 * @var array $params
 */

return [
    'yiisoft/error-handler' => [
        'solutionProviders' => [
            \Yiisoft\Definitions\Reference::to(\Yiisoft\ErrorHandler\Solution\FriendlyExceptionSolution::class),
        ]
    ]
];
