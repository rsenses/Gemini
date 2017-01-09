<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use App\Auth\AuraAuth;
use Slim\Interfaces\RouterInterface;

/**
 *
 */
class HomeController
{
    private $auth;
    private $logger;
    private $router;
    private $view;

    public function __construct(Twig $view, LoggerInterface $logger, AuraAuth $auth, RouterInterface $router)
    {
        $this->auth = $auth;
        $this->logger = $logger;
        $this->router = $router;
        $this->view = $view;
    }

    public function indexAction(Request $request, Response $response, array $args)
    {
        // Sample log message
        // $this->logger->info("Slim '/' route with DI");

        // Render index view
        if ($this->auth->getStatus() !== 'VALID') {
            return $response->withRedirect($this->router->pathFor('auth.signin'));
        } else {
            return $response->withRedirect($this->router->pathFor('dashboard'));
        }
    }
}
