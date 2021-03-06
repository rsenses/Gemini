<?php

// -----------------------------------------------------------------------------
// Action factories
// -----------------------------------------------------------------------------

$container['HomeController'] = function ($container) {
    return new App\Controllers\HomeController(
        $container->view,
        $container->logger,
        $container->auth,
        $container->router
    );
};

$container['AuthController'] = function ($container) {
    return new App\Controllers\Auth\AuthController(
        $container->view,
        $container->logger,
        $container->flash,
        $container->validator,
        $container->auth,
        $container->router
    );
};

$container['PasswordController'] = function ($container) {
    return new App\Controllers\Auth\PasswordController(
        $container->view,
        $container->logger,
        $container->flash,
        $container->validator,
        $container->auth,
        $container->router
    );
};

$container['DashboardController'] = function ($container) {
    return new App\Controllers\DashboardController(
        $container->view,
        $container->logger,
        $container->auth
    );
};

$container['ProjectController'] = function ($container) {
    return new App\Controllers\ProjectController(
        $container->view,
        $container->logger,
        $container->auth,
        $container->flash,
        $container->validator,
        $container->router
    );
};

$container['TaskController'] = function ($container) {
    return new App\Controllers\TaskController(
        $container->view,
        $container->logger,
        $container->auth,
        $container->flash,
        $container->validator,
        $container->router
    );
};

$container['TimeTrackController'] = function ($container) {
    return new App\Controllers\TimeTrackController(
        $container->view,
        $container->logger,
        $container->auth,
        $container->flash,
        $container->validator,
        $container->router
    );
};

$container['CommentController'] = function ($container) {
    return new App\Controllers\CommentController(
        $container->view,
        $container->logger,
        $container->auth,
        $container->flash,
        $container->validator,
        $container->router
    );
};

$container['NotificationController'] = function ($container) {
    return new App\Controllers\NotificationController(
        $container->view,
        $container->logger,
        $container->auth,
        $container->router
    );
};

$container['ClientController'] = function ($container) {
    return new App\Controllers\ClientController(
        $container->view,
        $container->logger,
        $container->auth,
        $container->flash,
        $container->validator,
        $container->router
    );
};

$container['UserController'] = function ($container) {
    return new App\Controllers\UserController(
        $container->view,
        $container->logger,
        $container->auth,
        $container->flash,
        $container->validator,
        $container->router,
        $container->csrf
    );
};


$container['ClientProjectController'] = function ($container) {
    return new App\Controllers\Client\ProjectController(
        $container->view,
        $container->logger,
        $container->auth,
        $container->flash,
        $container->validator,
        $container->router
    );
};
