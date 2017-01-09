<?php

namespace App;

use DI\ContainerBuilder;
use Interop\Container\ContainerInterface;

class App extends \DI\Bridge\Slim\App
{
    protected function configureContainer(ContainerBuilder $builder)
    {
        $definitions = [
            \Slim\Views\Twig::class => function (ContainerInterface $container) {
                $view = new \Slim\Views\Twig(__DIR__ . '/../templates', [
                    'cache' => __DIR__.'/../storage/cache/twig',
                    'debug' => true,
                    'auto_reload' => true,
                ]);

                $view->addExtension(new \Slim\Views\TwigExtension(
                    $container->get('router'),
                    $container->get('request')->getUri()
                ));

                return $view;
            },
            \Slim\Flash\Messages::class => function (ContainerInterface $container) {
                return new \Slim\Flash\Messages();
            },
            // \Monolog\Logger::class => function ($container) {
            //     $settings = $container->get('settings')['logger'];
            //     $logger = new Monolog\Logger($settings['name']);
            //     $logger->pushProcessor(new Monolog\Processor\UidProcessor());
            //     $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
            //     return $logger;
            // },
            // \App\Validation\Validator::class => function (ContainerInterface $container) {
            //     return new \App\Validation\Validator();
            // },
        ];

        $builder->addDefinitions($definitions);
    }
}

// $capsule = new \Illuminate\Database\Capsule\Manager();
// $capsule->addConnection($container->get('settings')['db']);
// $capsule->setAsGlobal();
// $capsule->bootEloquent();
//
// $container['db'] = function ($container) use ($capsule) {
//     return $capsule;
// };
//
// // Auth
// $container['auth'] = function ($container) {
//     return new \App\Auth\Auth();
// };
//
// // CSRF
// $container['csrf'] = function ($container) {
//     return new \Slim\Csrf\Guard();
// };
