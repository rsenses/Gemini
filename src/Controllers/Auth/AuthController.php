<?php

namespace App\Controllers\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use App\Validation\ValidatorInterface;
use App\Auth\AuraAuth;
use Exception;
use Slim\Interfaces\RouterInterface;
use Respect\Validation\Validator as v;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

use App\Entities\User;

class AuthController
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

    public function getSignInAction(Request $request, Response $response, array $args)
    {
        return $this->view->render($response, 'auth/signin.twig');
    }

    public function postSignInAction(Request $request, Response $response, array $args)
    {
        $validation = $this->validator->validate($request, [
            'email' => v::noWhitespace()->notEmpty()->email(),
            'password' => v::noWhitespace()->notEmpty()->length(8)->alnum('!·$%&/()=?¿¡^*+[]¨{},;.:-_#@'),
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.signin'));
        }

        try {
            $this->auth->loginService(
                filter_var($request->getParam('email'), FILTER_SANITIZE_EMAIL),
                filter_var($request->getParam('password'), FILTER_SANITIZE_STRING)
            );
        } catch (\Exception $e) {
            $this->flash->addMessage('danger', $e->getMessage());

            return $response->withRedirect($this->router->pathFor('auth.signin'));
        }

        return $response->withRedirect($this->router->pathFor('dashboard'));
    }

    public function getSignUpAction(Request $request, Response $response, array $args)
    {
        return $this->view->render($response, 'auth/signup.twig');
    }

    public function postSignUpAction(Request $request, Response $response, array $args)
    {
        $validation = $this->validator->validate($request, [
            'first_name' => v::notEmpty(),
            'last_name' => v::notEmpty(),
            'email' => v::noWhitespace()->notEmpty()->email()->unique('User', 'email'),
            'password' => v::noWhitespace()->notEmpty()->length(8)->alnum('!·$%&/()=?¿¡^*+[]¨{},;.:-_#@'),
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.signup'));
        }

        $user = User::create([
            'uuid' => Uuid::uuid1()->toString(),
            'email' => filter_var($request->getParam('email'), FILTER_SANITIZE_EMAIL),
            'first_name' => filter_var($request->getParam('first_name'), FILTER_SANITIZE_STRING),
            'last_name' => filter_var($request->getParam('last_name'), FILTER_SANITIZE_STRING),
            'password' => password_hash(filter_var($request->getParam('password'), FILTER_SANITIZE_STRING), PASSWORD_DEFAULT)
        ]);

        try {
            $this->auth->loginService(
                $user->email,
                filter_var($request->getParam('password'), FILTER_SANITIZE_STRING)
            );
        } catch (\Exception $e) {
            $this->flash->addMessage('danger', $e->getMessage());

            return $response->withRedirect($this->router->pathFor('auth.signin'));
        }

        $this->flash->addMessage('info', 'You have been signed up.');

        return $response->withRedirect($this->router->pathFor('home'));
    }

    public function getSignOutAction(Request $request, Response $response, array $args)
    {
        $logout = $this->auth->logoutService();

        if (!$logout) {
            $this->flash->addMessage('danger', 'You have not been signed out, sorry for the incovenience.');
        }

        return $response->withRedirect($this->router->pathFor('home'));
    }
}
