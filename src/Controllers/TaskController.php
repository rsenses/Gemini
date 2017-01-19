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
use JasonGrimes\Paginator;

use App\Entities\Client;
use App\Entities\Project;
use App\Entities\Task;
use App\Entities\User;

/**
 *
 */
class TaskController
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
        $tasks = Task::where('staff_id', $this->auth->getUserId())
            ->whereNull('done_at')
            ->whereNotNull('project_id')
            ->orderBy('due_at', 'ASC')
            ->get();

        foreach ($tasks as $task) {
            $task->totalTimeTrack = $this->totalTimeTrack($task);
        }

        return $this->view->render($response, 'task/all.twig', [
            'tasks' => $tasks
        ]);
    }

    public function completedUserAction(Request $request, Response $response, array $args)
    {
        $tasks = Task::where('staff_id', $this->auth->getUserId())
            ->whereNotNull('done_at')
            ->whereNotNull('project_id')
            ->orderBy('due_at', 'DESC')
            ->get();

        foreach ($tasks as $task) {
            $task->totalTimeTrack = $this->totalTimeTrack($task);
        }

        return $this->view->render($response, 'task/all.twig', [
            'tasks' => $tasks
        ]);
    }

    public function userAction(Request $request, Response $response, array $args)
    {
        $tasks = Task::where('staff_id', $args['id'])
            ->whereNull('done_at')
            ->whereNotNull('project_id')
            ->orderBy('due_at', 'ASC')
            ->get();

        foreach ($tasks as $task) {
            $task->totalTimeTrack = $this->totalTimeTrack($task);
        }

        return $this->view->render($response, 'task/all.twig', [
            'tasks' => $tasks
        ]);
    }

    public function unnassignedUserAction(Request $request, Response $response, array $args)
    {
        $tasks = Task::whereNull('staff_id')
            ->whereNull('done_at')
            ->whereNotNull('project_id')
            ->orderBy('due_at', 'ASC')
            ->get();

        foreach ($tasks as $task) {
            $task->totalTimeTrack = $this->totalTimeTrack($task);
        }

        return $this->view->render($response, 'task/all.twig', [
            'tasks' => $tasks
        ]);
    }

    public function assignedUserAction(Request $request, Response $response, array $args)
    {
        $tasks = Task::whereNotNull('staff_id')
            ->where('user_id', $this->auth->getUserId())
            ->where('staff_id', '!=', $this->auth->getUserId())
            ->whereNull('done_at')
            ->whereNotNull('project_id')
            ->orderBy('due_at', 'ASC')
            ->get();

        foreach ($tasks as $task) {
            $task->totalTimeTrack = $this->totalTimeTrack($task);
        }

        return $this->view->render($response, 'task/all.twig', [
            'tasks' => $tasks
        ]);
    }

    public function independentAction(Request $request, Response $response, array $args)
    {
        $tasks = Task::where('staff_id', $this->auth->getUserId())
            ->whereNull('done_at')
            ->whereNull('project_id')
            ->orderBy('due_at', 'ASC')
            ->get();

        foreach ($tasks as $task) {
            $task->totalTimeTrack = $this->totalTimeTrack($task);
        }

        return $this->view->render($response, 'task/all.twig', [
            'tasks' => $tasks
        ]);
    }

    public function independentCompletedAction(Request $request, Response $response, array $args)
    {
        $tasks = Task::where('staff_id', $this->auth->getUserId())
            ->whereNotNull('done_at')
            ->whereNull('project_id')
            ->orderBy('due_at', 'ASC')
            ->get();

        foreach ($tasks as $task) {
            $task->totalTimeTrack = $this->totalTimeTrack($task);
        }

        return $this->view->render($response, 'task/all.twig', [
            'tasks' => $tasks
        ]);
    }

    public function newAction(Request $request, Response $response, array $args)
    {
        $staff = User::where('email', 'LIKE', '%@expomark.es')
            ->orderBy('first_name', 'ASC')
            ->get();

        $projects = Project::whereNull('done_at')
            ->orderBy('name', 'ASC')
            ->get();

        return $this->view->render($response, 'task/new.twig', [
            'staff' => $staff,
            'projects' => $projects,
            'projectId' => isset($args['id']) ? $args['id'] : null,
        ]);
    }

    public function showAction(Request $request, Response $response, array $args)
    {
        $task = Task::findOrFail($args['id']);

        $totalTimeTrack = 0;
        foreach ($task->timetracks as $track) {
            $totalTimeTrack += (strtotime($track->updated_at) - strtotime($track->created_at));
        }

        return $this->view->render($response, 'task/show.twig', [
            'task' => $task,
            'totalTimeTrack' => $totalTimeTrack,
        ]);
    }

    public function editAction(Request $request, Response $response, array $args)
    {
        $task = Task::findOrFail($args['id']);

        $staff = User::where('email', 'LIKE', '%@expomark.es')
            ->orderBy('first_name', 'ASC')
            ->get();

        $projects = Project::whereNull('done_at')
            ->orderBy('name', 'ASC')
            ->get();

        $totalTimeTrack = 0;
        foreach ($task->timetracks as $track) {
            $totalTimeTrack += (strtotime($track->updated_at) - strtotime($track->created_at));
        }

        return $this->view->render($response, 'task/edit.twig', [
            'task' => $task,
            'staff' => $staff,
            'projects' => $projects,
            'totalTimeTrack' => $totalTimeTrack,
        ]);
    }

    public function completeAction(Request $request, Response $response, array $args)
    {
        $task = Task::findOrFail($args['id']);

        $task->done_at = Carbon::now();

        $task->save();

        return $response->withRedirect($this->router->pathFor('task.edit', [
            'id' => $args['id'],
        ]));
    }

    public function reopenAction(Request $request, Response $response, array $args)
    {
        $task = Task::findOrFail($args['id']);

        $task->done_at = null;

        $task->save();

        return $response->withRedirect($this->router->pathFor('task.edit', [
            'id' => $args['id'],
        ]));
    }

    public function saveAction(Request $request, Response $response, array $args)
    {
        $rules = [
            'name' => v::notEmpty(),
            'description' => v::notEmpty(),
            'project' => v::optional(v::intVal()),
            'due_at' => v::notEmpty()->min($request->getParam('started_at'))->date(),
        ];

        $validation = $this->validator->validate($request, $rules);

        if ($validation->failed()) {
            $this->flash->addMessage('danger', 'Tu tarea no ha sido guardada, revisa los errores en el formulario.');

            return $response->withRedirect($this->router->pathFor('task.new'));
        }

        $task = Task::create([
            'user_id' => $this->auth->getUserId(),
            'staff_id' => $request->getParam('staff') ? filter_var($request->getParam('staff'), FILTER_SANITIZE_NUMBER_INT) : null,
            'name' => filter_var($request->getParam('name'), FILTER_SANITIZE_STRING),
            'description' => $request->getParam('description'),
            'project_id' => $request->getParam('project') ? filter_var($request->getParam('project'), FILTER_SANITIZE_NUMBER_INT) : null,
            'due_at' => Carbon::createFromFormat('Y-m-d H:i', $request->getParam('due_at')),
        ]);

        if ($this->auth->getUserId() != $request->getParam('staff')) {
            $task->notifications()->create([
                'user_id' => $request->getParam('staff'),
                'description' => 'Nueva tarea',
            ]);
        }

        return $response->withRedirect($this->router->pathFor('task.show', [
            'id' => $task->task_id,
        ]));
    }

    public function modifyAction(Request $request, Response $response, array $args)
    {
        $rules = [
            'name' => v::notEmpty(),
            'description' => v::notEmpty(),
            'project' => v::optional(v::intVal()),
            'due_at' => v::notEmpty()->min($request->getParam('started_at'))->date(),
        ];

        $validation = $this->validator->validate($request, $rules);

        if ($validation->failed()) {
            $this->flash->addMessage('danger', 'Tu tarea no ha sido guardada, revisa los errores en el formulario.');

            return $response->withRedirect($this->router->pathFor('task.edit', [
                'id' => $args['id'],
            ]));
        }

        $task = Task::findOrFail($args['id']);

        $task->staff_id = $request->getParam('staff') ? filter_var($request->getParam('staff'), FILTER_SANITIZE_NUMBER_INT) : null;
        $task->name = filter_var($request->getParam('name'), FILTER_SANITIZE_STRING);
        $task->description = $request->getParam('description');
        $task->project_id = $request->getParam('project') ? filter_var($request->getParam('project'), FILTER_SANITIZE_NUMBER_INT) : null;
        $task->due_at = Carbon::createFromFormat('Y-m-d H:i', $request->getParam('due_at'));

        $task->save();

        return $response->withRedirect($this->router->pathFor('task.show', [
            'id' => $task->task_id,
        ]));
    }

    public function deleteAction(Request $request, Response $response, array $args)
    {
        $task = Task::findOrFail($args['id']);

        $task->notifications()->delete();

        $task->delete();

        return $response->withRedirect($this->router->pathFor('task.inprogress'));
    }

    private function totalTimeTrack($task)
    {
        $totalTimeTrack = 0;

        foreach ($task->timetracks as $track) {
            $totalTimeTrack += (strtotime($track->updated_at) - strtotime($track->created_at));
        }

        return $totalTimeTrack;
    }
}
