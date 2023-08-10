<?php
$params = require(__DIR__ . '/params.php');
$config = [
    'modules' => [
        /*
    'user' => [
        'class' => 'dektrium\user\Module',
        'enableUnconfirmedLogin' => false,
        'confirmWithin' => 21600,
        'cost' => 12,
        'admins' => ['richardbe']
    ],
*/
        'gridview' => ['class' => '\kartik\grid\Module'],
    ],
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'language' => 'es-ES', // <- here!
    'timeZone' => 'America/La_Paz',
    'bootstrap' => ['log'],

    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'jhdsfhudfhd6****656-/**/*/*/*/*-apX',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],

        'jasper' => [
            'class' => 'chrmorandi\jasper\Jasper',
            'redirect_output' => false, //optional
            'resource_directory' => false, //optional
            'locale' => 'es_ES', //optional
            'db' => [
                'dsn' => 'pgsql:host=181.177.143.180;port=5432;dbname=db_urkupina?sslmode=disable',
                // 'dsn' => 'pgsql:host=181.177.143.185;port=5432;dbname=test_urku?sslmode=disable',
                //'dsn' => 'pgsql:host=localhost;dbname=urkupina',                
                'username' => 'postgres',
                'password' => 'admin123',
                //'port'     => '5432'
                //'jdbcDir'  => 'D:\laragon\www\sistemaurkupina\vendor\chrmorandi\yii2-jasper\src\JasperStarter\jdbc\', 
                //'jdbcUrl'  => 'jdbc:postgresql:/181.177.143.185:5432/urkupinia'
            ]
        ],
        'user' => [
            //'identityClass' => 'app\models\User',
            'identityClass' => 'app\models\Usuario',
            'enableAutoLogin' => false,
            'enableSession' => true,
            'authTimeout' => 990,
            'loginUrl' => array('site/login'),
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'generadorQR' => [
            'class' => 'app\components\Qrcodetest',
        ],
        'httpClient' => [
            'class' => 'yii\httpclient\Client',
        ],
        'ruatServices' => [
            'class' => 'app\components\RuatServices'
        ],

        /*'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => false,
            'transport'=>[
                'class'=>'Swift_SmtpTransport',
                'host'=>'smtp.gmail.com',
                'username'=>'siscorso@gmail.com',            
                'password'=>'dtics2017',
                'port'=>'587',
                'encryption'=>'tls',
                         ],
        ],*/
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ], 
        ],
        */
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1', '192.168.220.103'],

    ];
}

return $config;
