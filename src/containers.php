<?php

// DIC configuration

$container = $app->getContainer();

// -----------------------------------------------------------------------------
// Service providers
// -----------------------------------------------------------------------------


// AuraAuth
$container['auth'] = function ($container) {
    return new App\Auth\AuraAuth(new Aura\Auth\AuthFactory($_COOKIE), $container->get('settings')['db']);
};
// Authorization
$container['authorization'] = function ($container) {
    return new App\Auth\Authorization($container->auth);
};

// Twig
$container['view'] = function ($container) {
    $settings = $container->get('settings')['view'];

    $view = new Slim\Views\Twig(
        $settings['template_path'],
        $settings['twig']
    );

    // Add filter
    // $gmdate = new Twig_Filter('gmdate', function ($string) {
    //     return gmdate('H:i:s', $string);
    // });
    //
    // $view->addFilter($gmdate);

    // Add extensions
    $view->addExtension(new Slim\Views\TwigExtension(
        $container->get('router'),
        $container->get('request')->getUri()
    ));
    $view->addExtension(new Twig_Extension_Debug());
    $view->addExtension(new Twig_Extensions_Extension_Date());

    $diff = new Twig_SimpleFilter('timediff', function ($date, $now = null) {
        $date = strtotime($date);

        $now = $now ? strtotime($now) : time();

        return date('H:i:s', $now - $date);
    });

    $view->getEnvironment()->addGlobal('auth', [
        'status' => $container->auth->getStatus(),
        'user' => $container->auth->getUserData(),
    ]);
    $view->getEnvironment()->addGlobal('authorization', $container->authorization);
    $view->getEnvironment()->addGlobal('flash', $container->flash);
    $view->getEnvironment()->addFilter($diff);

    return $view;
};

// Flash messages
$container['flash'] = function ($container) {
    return new \Slim\Flash\Messages();
};

// Validation
$container['validator'] = function ($container) {
    return new \App\Validation\Validator();
};

// Slugify
$container['slugify'] = function ($container) {
    return new Cocur\Slugify\Slugify();
};

// Eloquent
$capsule = new \Illuminate\Database\Capsule\Manager();
$capsule->addConnection($container->get('settings')['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function ($container) use ($capsule) {
    return $capsule;
};

// Language Negotiator
$container['negotiator'] = function ($container) {
    return new Negotiation\LanguageNegotiator();
};

// CSRF
$container['csrf'] = function ($container) {
    return new \Slim\Csrf\Guard();
};

// Monolog
$container['logger'] = function ($container) {
    $settings = $container->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};
