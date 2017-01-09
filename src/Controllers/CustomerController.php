<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use App\Auth\AuraAuth;
use App\Validation\ValidatorInterface;
use Respect\Validation\Validator as v;
use Exception;
use Slim\Interfaces\RouterInterface;
use Slim\Csrf\Guard;

use App\Entities\Customer;
use App\Entities\Product;
use App\Entities\User;

/**
 *
 */
class CustomerController
{
    private $auth;
    private $csrf;
    private $flash;
    private $logger;
    private $router;
    private $validator;
    private $view;

    public function __construct(Twig $view, LoggerInterface $logger, AuraAuth $auth, Messages $flash, ValidatorInterface $validator, RouterInterface $router, Guard $csrf)
    {
        $this->auth = $auth;
        $this->csrf = $csrf;
        $this->flash = $flash;
        $this->logger = $logger;
        $this->router = $router;
        $this->validator = $validator;
        $this->view = $view;
    }

    public function editAction(Request $request, Response $response, array $args)
    {
        $customer = Customer::find($args['id']);

        return $this->view->render($response, 'customer/edit.twig', [
            'customer' => $customer
        ]);
    }

    public function modifyAction(Request $request, Response $response, array $args)
    {
        $validation = $this->validator->validate($request, [
            'first_name' => v::notEmpty(),
            'last_name' => v::notEmpty(),
            'email' => v::noWhitespace()->notEmpty()->email()
        ]);

        if ($validation->failed()) {
            $errors = $_SESSION['validationErrors'];
            unset($_SESSION['validationErrors']);
            return $response->withJson([
                'status' => 'error',
                'errors' => $errors,
                'csrf_name' => $this->csrf->getTokenName(),
                'csrf_value' => $this->csrf->getTokenValue()
            ]);
        }

        try {
            $customer = Customer::find($args['id']);

            $customer->first_name = filter_var($request->getParam('first_name'), FILTER_SANITIZE_STRING);
            $customer->last_name = filter_var($request->getParam('last_name'), FILTER_SANITIZE_STRING);
            $customer->email = filter_var($request->getParam('email'), FILTER_SANITIZE_EMAIL);

            $customer->save();
        } catch (\Exception $e) {
            $this->flash->addMessage('danger', $e->getMessage());
        }

        return $response->withRedirect($this->router->pathFor('customer.edit', [
            'id' => $args['id']
        ]));
    }
}
