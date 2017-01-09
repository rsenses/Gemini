<?php
// Application middleware
// it's executed in an ascending order
// Slim 3 Middleware Execution Order (last in, first executed)

// e.g: $app->add(new \Slim\Csrf\Guard);

// $app->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware());

$app->add(new \App\Middlewares\BreadCrumbsMiddleware(
    $container->view,
    $container->router,
    $container->settings
));

$app->add(new \App\Middlewares\ValidationErrorsMiddleware(
    $container->view
));

$app->add(new \App\Middlewares\OldInputMiddleware(
    $container->view
));

$app->add(new \App\Middlewares\CsrfViewMiddleware(
    $container->view,
    $container->csrf
));

$app->add($container->csrf);

// Appending data to a view
$app->add(function ($request, $response, $next) {
    // Get rote name
    $route = $request->getAttribute('route');
    if (isset($route)) {
        $this->view->getEnvironment()->addGlobal('route_name', $route->getName());
    }
    $this->view->getEnvironment()->addGlobal('canonical', $request->getUri()->getPath());
    return $next($request, $response);
});
