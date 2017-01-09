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
class RegistrationController
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

    public function productAction(Request $request, Response $response, array $args)
    {
        $users = User::whereHas('products', function ($query) {
                $query->where('company_id', $this->auth->getClientId());
            })
            ->get();

        $product = Product::where('company_id', $this->auth->getClientId())
            ->where('product_id', $args['id'])
            ->firstOrFail();

        return $this->view->render($response, 'registration/product.twig', [
            'users' => $users,
            'product' => $product
        ]);
    }

    public function saveAction(Request $request, Response $response, array $args)
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
            $product = Product::where('company_id', $this->auth->getClientId())
                ->where('product_id', $args['id'])
                ->firstOrFail();
        } catch (\Exception $e) {
            $this->flash->addMessage('danger', $e->getMessage());

            return $response->withRedirect($this->router->pathFor('dashboard', [
                'lang' => $args['lang']
            ]));
        }

        try {
            $this->register($_POST, $product);
        } catch (Exception $e) {
            $this->flash->addMessage('danger', $e->getMessage());

            return $response->withRedirect($this->router->pathFor('dashboard', [
                'lang' => $args['lang']
            ]));
        }

        return $response->withJson([
            'status' => 'success'
        ]);
    }

    public function importAction(Request $request, Response $response, array $args)
    {
        try {
            $product = Product::where('company_id', $this->auth->getClientId())
                ->where('product_id', $args['id'])
                ->firstOrFail();
        } catch (\Exception $e) {
            $this->flash->addMessage('danger', $e->getMessage());

            return $response->withRedirect($this->router->pathFor('dashboard', [
                'lang' => $args['lang']
            ]));
        }

        $registration = [];

        $files = $request->getUploadedFiles();
        $csvFile = $files['csv'];

        if ($csvFile->getError() === UPLOAD_ERR_OK) {
            if ($csvFile->getClientMediaType() === 'text/csv') {
                if ($csvFile->getSize() < 500000) {
                    $csv = fopen($csvFile->file, 'r');
                    if ($csv) {
                        $counter = 0;
                        while (($data = fgetcsv($csv)) !== false) {
                            $registration[$counter]['first_name'] = $data[0];
                            $registration[$counter]['last_name'] = $data[1];
                            $registration[$counter]['email'] = $data[2];
                            $counter++;
                        }
                        fclose($csv);
                    }
                }
            }
        }

        unlink($csvFile->file);

        if (!empty($registration)) {
            foreach ($registration as $user) {
                try {
                    v::arrayVal()
                        ->key('first_name', v::notEmpty())
                        ->key('last_name', v::notEmpty())
                        ->key('email', v::noWhitespace()->notEmpty()->email())
                        ->assert($registration);
                } catch(\InvalidArgumentException $e) {
                    $messages = $e->findMessages([
                        'first_name' => 'Invalid first name',
                        'last_name' => 'Invalid last name',
                        'email' => 'Invalid email.'
                    ]);
                    $messages = array_filter($messages);
                    $messages = implode(', ', $messages);

                    $this->flash->addMessage('danger',
                        'Ha habido un error en la importación de usuarios con el siguiente mensaje:<br>'.$messages.'<br><br>Recuerde que el archivo CSV formato UTF-8 a importar debe contener, por orden, nombre, apellidos y un email válido.'
                    );

                    return $response->withRedirect($this->router->pathFor('registration.all', [
                        'lang' => $args['lang'],
                        'id' => $product->product_id,
                    ]));
                }
            }

            foreach ($registration as $user) {
                try {
                    $this->register($user, $product);
                } catch (Exception $e) {
                    $this->flash->addMessage('danger', $e->getMessage());
                }
            }

            return $response->withRedirect($this->router->pathFor('registration.all', [
                'lang' => $args['lang'],
                'id' => $product->product_id,
            ]));
        }
    }

    private function register(array $user, $product) {
        $customer = Customer::where('email', $user['email'])->first();

        if (!$customer) {
            $customer = Customer::create([
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
            ]);
        }

        $product->customers()->attach($customer, ['unique_id' => uniqid()]);

        return $response->withJson([
            'status' => 'success'
        ]);
    }

    public function removeAction(Request $request, Response $response, array $args)
    {
        $customer = Customer::find($args['customer']);

        $product = Product::find($args['product']);

        $product->customers()->detach($customer);
    }
}
