<?php

declare(strict_types=1);

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';


new yii\web\Application([
    'id'                  => 'loan-api-test',
    'basePath'            => dirname(__DIR__),
    'controllerNamespace' => 'app\http\controllers',
    'components'          => [
        'request'    => [
            'enableCookieValidation' => false,
            'scriptUrl'              => '/index.php',
        ],
        'response'   => ['format' => yii\web\Response::FORMAT_JSON],
        'urlManager' => [
            'enablePrettyUrl' => false,
            'showScriptName'  => true,
        ],
        'db' => [
            'class' => yii\db\Connection::class,
            'dsn'   => 'sqlite::memory:',
        ],
        'log' => [
            'targets' => [],
        ],
    ],
]);

Yii::$app->db->createCommand()->createTable('loan_requests', [
    'id'         => 'INTEGER PRIMARY KEY AUTOINCREMENT',
    'user_id'    => 'INTEGER NOT NULL',
    'amount'     => 'INTEGER NOT NULL',
    'term'       => 'INTEGER NOT NULL',
    'status'     => 'VARCHAR(20) NOT NULL DEFAULT \'pending\'',
    'created_at' => 'DATETIME',
    'updated_at' => 'DATETIME',
])->execute();
