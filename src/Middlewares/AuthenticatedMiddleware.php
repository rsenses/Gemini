<?php

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Flash\Messages;
use App\Auth\AuraAuth;
use Slim\Interfaces\RouterInterface;

class AuthenticatedMiddleware
{
    private $auth;
    private $flash;
    private $router;

    public function __construct(AuraAuth $auth, Messages $flash, RouterInterface $router)
    {
        $this->auth = $auth;
        $this->flash = $flash;
        $this->router = $router;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        if ($this->auth->getStatus() !== 'VALID') {
            $this->flash->addMessage('warning', 'Please sign in before going to your control panel.');

            return $response->withRedirect($this->router->pathFor('auth.signin'));
        }

        return $next($request, $response);
    }
}
