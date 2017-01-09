<?php
return [
    'settings' => [
        /**
         * Fixed Settings, never change
         */
        'determineRouteBeforeAppMiddleware' => true, // Allow middlewares determine route
        'session_name' => 'trackitAdminSessName',
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__.'/../storage/logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
        'allowedLocales' => [
            'es',
            // 'en',
        ],
        'locales' => [
            'es' => [
                'es_ES.UTF-8',
                'es_ES',
                'es',
                'spanish'
            ],
            'en' => [
                'en_US.UTF-8',
                'en_US',
                'en',
                'english'
            ],
        ],

        /**
         * Envirement Settings, change on server
         */
        'upload_path' => [
            'images' => __DIR__.'/../public/uploads/images/',
            'files' => __DIR__.'/../public/uploads/files/',
        ],
        'upload_url' => [
            'images' => 'https://gemini.expomark.es/uploads/images/',
            'files' => 'https://gemini.expomark.es/uploads/files/',
        ],
        'displayErrorDetails' => true, // set to false in production
        'debug' => true, // set to false in production
        'whoops.editor' => 'sublime', // Support click to open editor
        // Renderer Settings
        'view' => [
            'template_path' => __DIR__.'/../templates/',
            'twig' => [
                'cache' => __DIR__.'/../storage/cache/twig',
                'debug' => true, // set to false in production
                'auto_reload' => true,
            ],
        ],
        // DB Settings
        'db' => [
            'driver' => 'mysql',
            'host' => 'exlocalhost',
            'port' => '3306',
            'database' => 'gemini',
            'username' => 'root',
            'password' => '*Med1aS*',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'session_table' => 'session'
        ]
    ],
];
