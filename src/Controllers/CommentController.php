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

use App\Entities\Project;
use App\Entities\Task;
use App\Entities\Notification;

/**
 *
 */
class CommentController
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

    public function saveTaskAction(Request $request, Response $response, array $args)
    {
        $task = Task::findOrFail($args['id']);

        $rules = [
            'description' => v::notEmpty(),
        ];

        $validation = $this->validator->validate($request, $rules);

        if ($validation->failed()) {
            return $response->withHeader('X-IC-Trigger', json_encode([
                'show.alert.modal' => [
                    'alert-danger',
                    'Por favor, escribe algo para poder guardar el comentario.'
                ]
            ]));
        }

        $comment = $task->comments()->create([
            'description' => $request->getParam('description'),
            'user_id' => $this->auth->getUserId(),
        ]);

        if ($this->auth->getUserId() != $task->user->user_id) {
            $notification = $task->notifications()
                ->where('user_id', $task->user->user_id)
                ->where('is_readed', 1)
                ->count();

            if (!$notification) {
                $task->notifications()->create([
                    'user_id' => $task->user->user_id,
                    'description' => 'Nuevo comentario en '.$task->name,
                ]);
            }
        }

        foreach ($task->project->users()->get() as $user) {
            if ($this->auth->getUserId() != $user->user_id) {
                $notification = $task->notifications()
                    ->where('user_id', $user->user_id)
                    ->where('is_readed', 1)
                    ->count();

                if (!$notification) {
                    $task->notifications()->create([
                        'user_id' => $user->user_id,
                        'description' => 'Nuevo comentario en '.$task->name,
                    ]);
                }
            }
        }

        return $response->withRedirect($this->router->pathFor('task.show', [
            'id' => $task->task_id,
        ]));

        // return $response->withAddedHeader('X-IC-Script', 'location.reload();')
        //     ->withHeader('X-IC-Trigger', json_encode([
        //     'show.alert.modal' => [
        //         'alert-success',
        //         'Cliente guardado correctamente.'
        //     ]
        // ]));
    }

    public function saveProjectAction(Request $request, Response $response, array $args)
    {
        $project = Project::findOrFail($args['id']);

        $rules = [
            'description' => v::notEmpty(),
        ];

        $validation = $this->validator->validate($request, $rules);

        if ($validation->failed()) {
            return $response->withHeader('X-IC-Trigger', json_encode([
                'show.alert.modal' => [
                    'alert-danger',
                    'Por favor, escribe algo para poder guardar el comentario.'
                ]
            ]));
        }

        $comment = $project->comments()->create([
            'description' => $request->getParam('description'),
            'user_id' => $this->auth->getUserId(),
        ]);

        if ($this->auth->getUserId() != $project->user->user_id) {
            $notification = $project->notifications()
                ->where('user_id', $project->user->user_id)
                ->where('is_readed', 1)
                ->count();

            if (!$notification) {
                $project->notifications()->create([
                    'user_id' => $project->user->user_id,
                    'description' => 'Nuevo comentario en '.$project->name,
                ]);
            }
        }

        foreach ($project->users()->get() as $user) {
            if ($this->auth->getUserId() != $user->user_id) {
                $notification = $project->notifications()
                    ->where('user_id', $user->user_id)
                    ->where('is_readed', 1)
                    ->count();

                if (!$notification) {
                    $project->notifications()->create([
                        'user_id' => $user->user_id,
                        'description' => 'Nuevo comentario en '.$project->name,
                    ]);
                }
            }
        }

        return $response->withRedirect($this->router->pathFor('project.show', [
            'id' => $project->project_id,
        ]));

        // return $response->withAddedHeader('X-IC-Script', 'location.reload();')
        //     ->withHeader('X-IC-Trigger', json_encode([
        //     'show.alert.modal' => [
        //         'alert-success',
        //         'Cliente guardado correctamente.'
        //     ]
        // ]));
    }
}
