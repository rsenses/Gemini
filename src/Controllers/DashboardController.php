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
        $client = $this->auth->getClientId();

        if ($client) {
            $tags = $this->auth->getTags();

            $projects = Project::where('client_id', $client)
                ->where('is_active', 1)
                ->whereNull('done_at')
                ->whereHas('tags', function ($query) use ($tags) {
                    $query->whereIn('project_tag.tag_id', $tags);
                })
                ->orderBy('due_at', 'ASC')
                ->get();
        } else {
            $projects = Project::where('user_id', $this->auth->getUserId())
                ->where('is_active', 1)
                ->whereNull('done_at')
                ->orderBy('due_at', 'ASC')
                ->get();
        }

        return $this->view->render($response, 'dashboard/dashboard.twig', [
            'projects' => $projects
        ]);
    }
}
