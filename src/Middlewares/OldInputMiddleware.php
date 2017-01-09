<?php

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class OldInputMiddleware
{
    private $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        if (isset($_SESSION['oldInput'])) {
            $this->view->getEnvironment()->addGlobal('old_input', $_SESSION['oldInput']);
        }
        $_SESSION['oldInput'] = $request->getParams();

        return $next($request, $response);
    }
}
