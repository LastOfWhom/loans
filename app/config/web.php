<?php

declare(strict_types=1);

use app\repositories\interfaces\LoanRequestRepositoryInterface;
use app\repositories\LoanRequestRepository;
use app\services\interfaces\LoanProcessorServiceInterface;
use app\services\interfaces\LoanRequestServiceInterface;
use app\services\LoanProcessorService;
use app\services\LoanRequestService;

$db = require __DIR__ . '/db.php';

return [
    'id'                  => 'loan-api',
    'basePath'            => dirname(__DIR__),
    'controllerNamespace' => 'app\controllers',

    /*
     * Dependency Injection container configuration.
     * Maps interfaces to concrete implementations for constructor injection.
     */
    'container' => [
        'definitions' => [
            LoanRequestRepositoryInterface::class => LoanRequestRepository::class,
            LoanRequestServiceInterface::class    => LoanRequestService::class,
            LoanProcessorServiceInterface::class  => LoanProcessorService::class,
        ],
    ],

    'components' => [
        'request' => [
            'enableCookieValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],

        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
        ],

        'db' => $db,

        'urlManager' => [
            'enablePrettyUrl'     => true,
            'showScriptName'      => false,
            'enableStrictParsing' => true,
            'rules'               => [
                'POST requests' => 'requests/create',
                'GET processor' => 'processor/index',
            ],
        ],

        'errorHandler' => [
            'class' => 'yii\web\ErrorHandler',
        ],

        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
    ],
];
