<?php

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Flash\Messages;
use App\Auth\AuraAuth;
use Slim\Views\Twig;

use App\Entities\TimeTrack;

class TimeTrackMiddleware
{
    private $auth;
    private $view;

    public function __construct(AuraAuth $auth, Twig $view)
    {
        $this->auth = $auth;
        $this->view = $view;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        $staffId = $this->auth->getUserId();

        $activeTimeTrack = TimeTrack::whereHas('task', function($q) use ($staffId) {
                $q->where('task.staff_id', $staffId);
            })
            ->where('is_completed', 0)
            ->count();

         $this->view->getEnvironment()->addGlobal('active_timetrack', $activeTimeTrack);

        return $next($request, $response);
    }
}
