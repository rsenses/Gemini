<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Auth\AuraAuth;
use Slim\Flash\Messages;
use App\Validation\ValidatorInterface;
use Respect\Validation\Validator as v;
use Slim\Interfaces\RouterInterface;
use Exception;
use Carbon\Carbon;
use Slim\Csrf\Guard;

use App\Entities\Product;
use App\Entities\User;

/**
 *
 */
class UserController
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

    public function allAction(Request $request, Response $response, array $args)
    {
        $companyId = $this->auth->getClientId();

        $users = User::whereHas('products', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->orderBy('last_name', 'ASC')
            ->get();

        return $this->view->render($response, 'user/all.twig', [
            'users' => $users
        ]);
    }

    public function editAction(Request $request, Response $response, array $args)
    {
        $user = User::find($args['id']);

        return $this->view->render($response, 'user/edit.twig', [
            'user' => $user
        ]);
    }

    public function addAction(Request $request, Response $response, array $args)
    {
        try {
            $product = Product::where('company_id', $this->auth->getClientId())
                ->where('product_id', $args['id'])
                ->firstOrFail();
        } catch (\Exception $e) {
            $this->flash->addMessage('danger', $e->getMessage());

            return $response->withRedirect($this->router->pathFor('dashboard'));
        }

        $users = User::whereHas('products', function ($query) {
                $query->where('company_id', $this->auth->getClientId());
            })
            ->get();

        $staff = $request->getParam('staff');

        $staffValidator = v::arrayType()->each(v::intVal())->validate($staff);

        if ($staffValidator) {
            foreach ($staff as $userId) {
                $user = User::find($userId);

                $product->users()->attach($user, [
                    'date_start' => $product->date_start,
                    'date_end' => $product->date_end
                ]);
            }

            return $response->withJson([
                'status' => 'success'
            ]);
        } else {
            return $response->withJson([
                'status' => 'error',
                'errors' => 'Error al asignar usuarios, inténtelo de nuevo más tarde.',
                'csrf_name' => $this->csrf->getTokenName(),
                'csrf_value' => $this->csrf->getTokenValue()
            ]);
        }
    }

    public function modifyAction(Request $request, Response $response, array $args)
    {
        $validation = $this->validator->validate($request, [
            'first_name' => v::notEmpty(),
            'last_name' => v::notEmpty(),
            'email' => v::noWhitespace()->notEmpty()->email(),
            'phone' => v::noWhitespace()->notEmpty()->phone()
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
            $user = User::find($args['id']);

            $user->first_name = filter_var($request->getParam('first_name'), FILTER_SANITIZE_STRING);
            $user->last_name = filter_var($request->getParam('last_name'), FILTER_SANITIZE_STRING);
            $user->email = filter_var($request->getParam('email'), FILTER_SANITIZE_EMAIL);
            $user->phone = filter_var($request->getParam('phone'), FILTER_SANITIZE_STRING);

            $user->save();
        } catch (\Exception $e) {
            $this->flash->addMessage('danger', $e->getMessage());
        }

        return $response->withRedirect($this->router->pathFor('user.edit', [
            'id' => $args['id']
        ]));
    }
}
