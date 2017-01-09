<?php

namespace App\Controllers\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use App\Validation\ValidatorInterface;
use App\Auth\AuraAuth;
use Slim\Interfaces\RouterInterface;

use App\Entities\User;
use Respect\Validation\Validator as v;

/**
 *
 */
class PasswordController
{
    private $auth;
    private $flash;
    private $logger;
    private $router;
    private $validator;
    private $view;

    public function __construct(Twig $view, LoggerInterface $logger, Messages $flash, ValidatorInterface $validator, AuraAuth $auth, RouterInterface $router)
    {
        $this->auth = $auth;
        $this->flash = $flash;
        $this->logger = $logger;
        $this->router = $router;
        $this->validator = $validator;
        $this->view = $view;
    }

    public function getChangePasswordAction(Request $request, Response $response, array $args)
    {
        return $this->view->render($response, 'auth/change-password.twig');
    }

    public function postChangePasswordAction(Request $request, Response $response, array $args)
    {
        $validation = $this->validator->validate($request, [
            'password' => v::noWhitespace()->notEmpty()->length(8)->alnum('!·$%&/()=?¿¡^*+[]¨{},;.:-_#@')->matchesPassword($this->auth->getUserData()->password),
            'new_password' => v::noWhitespace()->notEmpty()->length(8)->alnum('!·$%&/()=?¿¡^*+[]¨{},;.:-_#@'),
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.change.password'));
        }

        $this->auth->getUserData()->setPassword(filter_var($request->getParam('new_password'), FILTER_SANITIZE_STRING));

        $this->flash->addMessage('info', 'Your Password was changed.');

        return $response->withRedirect($this->router->pathFor('home'));
    }
}
