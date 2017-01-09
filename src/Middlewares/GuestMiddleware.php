<?php

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Auth\AuraAuth;
use Slim\Interfaces\RouterInterface;

class GuestMiddleware
{
    private $auth;
    private $router;

    public function __construct(AuraAuth $auth, RouterInterface $router)
    {
        $this->auth = $auth;
        $this->router = $router;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        if ($this->auth->getStatus() === 'VALID') {
            return $response->withRedirect($this->router->pathFor('home'));
        }

        return $next($request, $response);
    }
}
