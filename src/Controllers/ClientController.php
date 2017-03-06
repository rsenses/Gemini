<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Auth\AuraAuth;
use App\Upload\Upload;
use Slim\Flash\Messages;
use App\Validation\ValidatorInterface;
use Respect\Validation\Validator as v;
use Slim\Interfaces\RouterInterface;
use Exception;
use Cocur\Slugify\Slugify;
use Carbon\Carbon;
use Slim\Csrf\Guard;

use App\Entities\Client;

/**
 *
 */
class ClientController
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

    public function saveAction(Request $request, Response $response, array $args)
    {
        $rules = [
            'name' => v::notEmpty(),
        ];

        $validation = $this->validator->validate($request, $rules);

        if ($validation->failed()) {
            return $response->withHeader('X-IC-Trigger', json_encode([
                'show.alert.modal' => [
                    'alert-danger',
                    'El cliente no ha sido guardado, el nombre es obligatorio.'
                ]
            ]));
        }

        $client = Client::create([
            'name' => filter_var($request->getParam('name'), FILTER_SANITIZE_STRING),
        ]);

        return $response->withAddedHeader('X-IC-Script', "\$('#client').append(new Option('".$client->name."', ".$client->client_id.", true, true)).trigger('change');setTimeout(function(){\$('#newClient').modal('hide');}, 3000);")
            ->withHeader('X-IC-Trigger', json_encode([
            'show.alert.modal' => [
                'alert-success',
                'Cliente guardado correctamente.'
            ]
        ]));
    }
}
