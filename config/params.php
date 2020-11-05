<?php
return [
    'yiisoft/error-handler' => [
        'htmlRenderer' => [
            'templates' => [
                'default' => [
                    'callStackItem',
                    'error',
                    'exception',
                    'previousException',
                ],
            ],
        ],
    ],
];
