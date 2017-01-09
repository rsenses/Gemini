<?php

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Csrf\Guard;
use Slim\Views\Twig;

class CsrfViewMiddleware
{
    private $csrf;
    private $view;

    public function __construct(Twig $view, Guard $csrf)
    {
        $this->csrf = $csrf;
        $this->view = $view;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        $this->view->getEnvironment()->addGlobal('csrf', [
            'field' => '
                <input type="hidden" name="'.$this->csrf->getTokenNameKey().'" value="'.$this->csrf->getTokenName().'">
                <input type="hidden" name="'.$this->csrf->getTokenValueKey().'" value="'.$this->csrf->getTokenValue().'">
            ',
        ]);

        return $next($request, $response);
    }
}
