<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Auth\AuraAuth;
use Slim\Flash\Messages;
use App\Validation\ValidatorInterface;
use Slim\Interfaces\RouterInterface;

use App\Entities\TimeTrack;
use App\Entities\Task;

/**
 *
 */
class TimeTrackController
{
    private $auth;
    private $flash;
    private $logger;
    private $router;
    private $validator;
    private $view;

    public function __construct(Twig $view, LoggerInterface $logger, AuraAuth $auth, Messages $flash, ValidatorInterface $validator, RouterInterface $router)
    {
        $this->auth = $auth;
        $this->flash = $flash;
        $this->logger = $logger;
        $this->router = $router;
        $this->validator = $validator;
        $this->view = $view;
    }

    public function startAction(Request $request, Response $response, array $args)
    {
        $timeTrack = TimeTrack::create([
            'task_id' => $args['id'],
            'is_completed' => 0,
        ]);

        $task = Task::findOrFail($args['id']);

        return $response->withRedirect($this->router->pathFor('task.show', [
            'id' => $args['id'],
        ]));
    }

    public function stopAction(Request $request, Response $response, array $args)
    {
        $userId = $this->auth->getUserId();

        $timeTrack = TimeTrack::where('is_completed', 0)
            ->whereHas('task', function ($query) use ($userId) {
                $query->where('task.staff_id', $userId);
            })
            ->firstOrFail();

        $timeTrack->is_completed = 1;

        $timeTrack->save();

        return $response->withRedirect($this->router->pathFor('task.show', [
            'id' => $timeTrack->task_id,
        ]));
    }

    public function modifyAction(Request $request, Response $response, array $args)
    {
        $timeTrack = TimeTrack::findOrFail($args['id']);

        if ($request->getParam('created_at')) {
            $timeTrack->created_at = $request->getParam('created_at');
        }

        if ($request->getParam('updated_at')) {
            $timeTrack->updated_at = $request->getParam('updated_at');
        }

        $timeTrack->save();

        return $response->withHeader('X-IC-Redirect', $this->router->pathFor('task.show', [
            'id' => $timeTrack->task_id,
        ]));
    }

    public function deleteAction(Request $request, Response $response, array $args)
    {
        $timeTrack = TimeTrack::findOrFail($args['id']);

        $task_id = $timeTrack->task_id;

        $timeTrack->delete();

        return $response->withRedirect($this->router->pathFor('task.show', [
            'id' => $task_id,
        ]));
    }
}
