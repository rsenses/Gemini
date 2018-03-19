<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Auth\AuraAuth;
use Carbon\Carbon;

use App\Entities\Project;
use App\Entities\Task;
use App\Entities\User;

/**
 *
 */
class DashboardController
{
    private $view;
    private $logger;
    private $auth;

    public function __construct(Twig $view, LoggerInterface $logger, AuraAuth $auth)
    {
        $this->view = $view;
        $this->logger = $logger;
        $this->auth = $auth;
    }

    public function indexAction(Request $request, Response $response, array $args)
    {
        $projects = Project::where('user_id', $this->auth->getUserId())
            ->whereNull('done_at')
            ->orderBy('due_at', 'ASC')
            ->get();

        return $this->view->render($response, 'dashboard/dashboard.twig', [
            'projects' => $projects
        ]);
    }
}
