<?php

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class ValidationErrorsMiddleware
{
    private $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        if (isset($_SESSION['validationErrors'])) {
            $this->view->getEnvironment()->addGlobal('validation_errors', $_SESSION['validationErrors']);
            unset($_SESSION['validationErrors']);
        }

        return $next($request, $response);
    }
}
