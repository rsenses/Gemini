<?php

namespace App\Controllers\Client;

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
use JasonGrimes\Paginator;

use App\Entities\Client;
use App\Entities\Project;
use App\Entities\Tag;
use App\Entities\Task;
use App\Entities\User;

/**
 *
 */
class ProjectController
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

    public function inProgressAction(Request $request, Response $response, array $args)
    {
        $client = $this->auth->getClientId();

        $tags = $this->auth->getTags();

        $projects = Project::where('client_id', $client)
            ->where('is_active', 1)
            ->whereHas('tags', function ($query) use ($tags) {
                $query->whereIn('project_tag.tag_id', $tags);
            })
            ->orderBy('due_at', 'ASC')
            ->get();

        return $this->view->render($response, 'project/projects.twig', [
            'projects' => $projects,
        ]);
    }
}
