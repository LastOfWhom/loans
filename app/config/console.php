<?php

declare(strict_types=1);

$db = require __DIR__ . '/db.php';

return [
    'id'                  => 'loan-api-console',
    'basePath'            => dirname(__DIR__),
    'controllerNamespace' => 'app\commands',
    'components'          => [
        'db' => $db,
        'log' => [
            'targets' => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
    ],
];
