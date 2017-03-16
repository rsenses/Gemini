<?php
return [
    'settings' => [
        /**
         * Fixed Settings, never change
         */
        'determineRouteBeforeAppMiddleware' => true, // Allow middlewares determine route
        'session_name' => 'geminiSessName',
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__.getenv('PATH_LOGGER'),
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
            'images' => __DIR__.getenv('PATH_UPLOADS').'images/',
            'files' => __DIR__.getenv('PATH_UPLOADS').'files/',
        ],
        'upload_url' => [
            'images' => 'https://gemini.expomark.es/uploads/images/',
            'files' => 'https://gemini.expomark.es/uploads/files/',
        ],
        'displayErrorDetails' => (bool) getenv('DISPLAY_ERRORS'), // set to false in production
        'debug' => (bool) getenv('DISPLAY_ERRORS'), // set to false in production
        'whoops.editor' => 'sublime', // Support click to open editor
        // Renderer Settings
        'view' => [
            'template_path' => __DIR__.getenv('PATH_TEMPLATES'),
            'twig' => [
                'cache' => __DIR__.getenv('PATH_TWIG'),
                'debug' => true, // set to false in production
                'auto_reload' => true,
            ],
        ],
        // DB Settings
        'db' => [
            'driver' => getenv('DB_DRIVER'),
            'host' => getenv('DB_HOST'),
            'port' => (int) getenv('DB_PORT'),
            'database' => getenv('DB_NAME'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'session_table' => 'session'
        ]
    ],
];
