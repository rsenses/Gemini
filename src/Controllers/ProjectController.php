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
use Carbon\Carbon;

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

    public function inProgressAllUsersAction(Request $request, Response $response, array $args)
    {
        $projects = Project::whereNull('done_at')
            ->where('is_active', 1)
            ->orderBy('due_at', 'ASC')
            ->get();

        return $this->view->render($response, 'project/projects.twig', [
            'projects' => $projects,
        ]);
    }

    public function inProgressAction(Request $request, Response $response, array $args)
    {
        $staffId = $this->auth->getUserId();

        $projects = Project::whereNull('done_at')
            ->where('is_active', 1)
            ->where(function ($q) use ($staffId) {
                $q->where('project.user_id', $staffId);
                $q->orWhereHas('users', function ($q) use ($staffId) {
                    $q->where('user.user_id', $staffId);
                });
            })
            ->orderBy('due_at', 'ASC')
            ->get();

        return $this->view->render($response, 'project/projects.twig', [
            'projects' => $projects
        ]);
    }

    public function limboAction(Request $request, Response $response, array $args)
    {
        $staffId = $this->auth->getUserId();

        $projects = Project::whereNull('done_at')
            ->where('is_active', 0)
            ->where(function ($q) use ($staffId) {
                $q->where('project.user_id', $staffId);
                $q->orWhereHas('users', function ($q) use ($staffId) {
                    $q->where('user.user_id', $staffId);
                });
            })
            ->orderBy('due_at', 'ASC')
            ->get();

        return $this->view->render($response, 'project/projects.twig', [
            'projects' => $projects
        ]);
    }

    public function limboAllUsersAction(Request $request, Response $response, array $args)
    {
        $staffId = $this->auth->getUserId();

        $projects = Project::whereNull('done_at')
            ->where('is_active', 0)
            ->orderBy('due_at', 'ASC')
            ->get();

        return $this->view->render($response, 'project/projects.twig', [
            'projects' => $projects
        ]);
    }

    public function inProgressCalendarAction(Request $request, Response $response, array $args)
    {
        return $this->view->render($response, 'project/calendar.twig');
    }

    public function inProgressCalendarJsonAction(Request $request, Response $response, array $args)
    {
        $staffId = $this->auth->getUserId();

        $projects = Project::whereNull('done_at')
            ->whereNotNull('started_at')
            ->whereNotNull('due_at')
            ->where('is_active', 1)
            ->where(function ($q) use ($staffId) {
                $q->where('project.user_id', $staffId);
                $q->orWhereHas('users', function ($q) use ($staffId) {
                    $q->where('user.user_id', $staffId);
                });
            })
            ->whereDoesntHave('tags', function ($query) {
                $query->where('tag.slug', '=', 'mantenimiento')
                    ->orWhere('tag.slug', '=', 'contenidos')
                    ->orWhere('tag.slug', '=', 'colaboradores');
            })
            ->orderBy('due_at', 'ASC')
            ->get();

        $data = [];
        foreach ($projects as $project) {
            $data[] = [
                'title' => $project->name,
                'start' => $project->started_at->toDateString(),
                'end' => $project->due_at->toDateString(),
                'color' => $project->color,
                'url' => '/project/show/' . $project->project_id,
            ];
        }

        return $response->withJson($data);
    }

    public function completedAction(Request $request, Response $response, array $args)
    {
        $projects = Project::whereNull('bill')
            ->whereNotNull('done_at')
            ->orderBy('due_at', 'DESC')
            ->get();

        return $this->view->render($response, 'project/projects.twig', [
            'projects' => $projects
        ]);
    }

    public function searchAction(Request $request, Response $response, array $args)
    {
        $q = $request->getQueryParam('q');

        $projects = Project::whereRaw('MATCH (name, description) AGAINST (?)', [$q])
            ->orWhere('project_id', $q)
            ->orWhereHas('client', function ($query) use ($q) {
                $query->where('name', 'like', '%' . $q . '%');
            })
            ->orderByRaw('(`project`.`due_at` < CURDATE()),
                (CASE WHEN `project`.`due_at` > CURDATE() THEN `project`.`due_at` end) ASC,
                (CASE WHEN `project`.`due_at` < CURDATE() THEN `project`.`due_at` end) DESC')
            ->get();

        return $this->view->render($response, 'project/projects.twig', [
            'projects' => $projects,
            'q' => $q
        ]);
    }

    public function billedAction(Request $request, Response $response, array $args)
    {
        $page = isset($args['page']) ? $args['page'] : 1;

        $projects = Project::whereNotNull('bill')
            ->whereNotNull('done_at')
            ->get();

        return $this->view->render($response, 'project/billed.twig', [
            'projects' => $projects
        ]);
    }

    public function userAction(Request $request, Response $response, array $args)
    {
        $projects = Project::where('user_id', $args['id'])
            ->whereNull('done_at')
            ->orderBy('due_at', 'ASC')
            ->get();

        return $this->view->render($response, 'project/projects.twig', [
            'projects' => $projects,
        ]);
    }

    public function clientAction(Request $request, Response $response, array $args)
    {
        $projects = Project::where('client_id', $args['id'])
            ->whereNull('done_at')
            ->orderBy('due_at', 'ASC')
            ->get();

        return $this->view->render($response, 'project/projects.twig', [
            'projects' => $projects
        ]);
    }

    public function tagAction(Request $request, Response $response, array $args)
    {
        $tagId = $args['id'];

        $projects = Project::whereHas('tags', function ($query) use ($tagId) {
            $query->where('tag.tag_id', $tagId);
        })
            ->whereNull('done_at')
            ->orderBy('due_at', 'ASC')
            ->get();

        return $this->view->render($response, 'project/projects.twig', [
            'projects' => $projects
        ]);
    }

    public function newAction(Request $request, Response $response, array $args)
    {
        $clients = Client::orderBy('name', 'ASC')->get();

        $staff = User::where('is_active', 1)
            ->where(function ($q) {
                $q->where('email', 'LIKE', '%@expomark.es')
                    ->orWhere('email', 'LIKE', '%@metech.es');
            })
            ->orderBy('first_name', 'ASC')
            ->get();

        $tags = Tag::orderBy('slug', 'ASC')->get();

        return $this->view->render($response, 'project/new.twig', [
            'clients' => $clients,
            'staff' => $staff,
            'tags' => $tags,
        ]);
    }

    public function printAction(Request $request, Response $response, array $args)
    {
        $project = Project::findOrFail($args['id']);

        return $this->view->render($response, 'project/print/all.twig', [
            'project' => $project
        ]);
    }

    public function printTagAction(Request $request, Response $response, array $args)
    {
        $project = Project::findOrFail($args['id']);

        $project->is_printed = 1;

        $project->save();

        return $this->view->render($response, 'project/print/tag.twig', [
            'project' => $project
        ]);
    }

    public function showAction(Request $request, Response $response, array $args)
    {
        $project = Project::findOrFail($args['id']);

        $inProgressTasks = Task::where('project_id', $project->project_id)
            ->whereNull('done_at')
            ->orderBy('due_at', 'ASC')
            ->get();

        $project->inProgressTasksTime = 0;

        foreach ($inProgressTasks as $task) {
            $totalTimeTrack = 0;

            foreach ($task->timetracks as $track) {
                $totalTimeTrack += (strtotime($track->updated_at) - strtotime($track->created_at));
            }

            $task->totalTimeTrack = $totalTimeTrack;

            $project->inProgressTasksTime += $totalTimeTrack;
        }

        $seconds = $project->inProgressTasksTime;

        $hours = floor($seconds / 3600);
        $seconds -= $hours * 3600;
        $minutes = floor($seconds / 60);
        $seconds -= $minutes * 60;

        $project->inProgressTasksTime = "$hours:$minutes:$seconds";

        $completedTasks = Task::where('project_id', $project->project_id)
            ->whereNotNull('done_at')
            ->orderBy('due_at', 'ASC')
            ->get();

        $project->completedTasksTime = 0;

        foreach ($completedTasks as $task) {
            $totalTimeTrack = 0;

            foreach ($task->timetracks as $track) {
                $totalTimeTrack += strtotime($track->updated_at) - strtotime($track->created_at);
            }

            $task->totalTimeTrack = $totalTimeTrack;

            $project->completedTasksTime += $totalTimeTrack;
        }

        $seconds = $project->completedTasksTime;

        $hours = floor($seconds / 3600);
        $seconds -= $hours * 3600;
        $minutes = floor($seconds / 60);
        $seconds -= $minutes * 60;

        $project->completedTasksTime = "$hours:$minutes:$seconds";

        return $this->view->render($response, 'project/show.twig', [
            'project' => $project,
            'inProgressTasks' => $inProgressTasks,
            'completedTasks' => $completedTasks,
        ]);
    }

    public function editAction(Request $request, Response $response, array $args)
    {
        $project = Project::findOrFail($args['id']);

        $clients = Client::orderBy('name', 'ASC')->get();

        $tags = Tag::orderBy('slug', 'ASC')->get();

        $staff = User::where('is_active', 1)
            ->where(function ($q) {
                $q->where('email', 'LIKE', '%@expomark.es')
                    ->orWhere('email', 'LIKE', '%@metech.es');
            })
            ->orderBy('first_name', 'ASC')
            ->get();

        $inProgressTasks = Task::where('project_id', $project->project_id)
            ->whereNull('done_at')
            ->orderBy('due_at', 'ASC')
            ->get();

        $project->inProgressTasksTime = 0;

        foreach ($inProgressTasks as $task) {
            $totalTimeTrack = 0;

            foreach ($task->timetracks as $track) {
                $totalTimeTrack += (strtotime($track->updated_at) - strtotime($track->created_at));
            }

            $task->totalTimeTrack = $totalTimeTrack;

            $project->inProgressTasksTime += $totalTimeTrack;
        }

        $completedTasks = Task::where('project_id', $project->project_id)
            ->whereNotNull('done_at')
            ->orderBy('due_at', 'ASC')
            ->get();

        $project->completedTasksTime = 0;

        foreach ($completedTasks as $task) {
            $totalTimeTrack = 0;

            foreach ($task->timetracks as $track) {
                $totalTimeTrack += (strtotime($track->updated_at) - strtotime($track->created_at));
            }

            $task->totalTimeTrack = $totalTimeTrack;

            $project->completedTasksTime += $totalTimeTrack;
        }

        return $this->view->render($response, 'project/edit.twig', [
            'project' => $project,
            'clients' => $clients,
            'tags' => $tags,
            'staff' => $staff,
            'inProgressTasks' => $inProgressTasks,
            'completedTasks' => $completedTasks,
        ]);
    }

    public function toggleActiveAction(Request $request, Response $response, array $args)
    {
        $project = Project::findOrFail($args['id']);

        $project->is_active = !$project->is_active;

        $project->save();

        return $response->withRedirect($this->router->pathFor('project.show', [
            'id' => $args['id'],
        ]));
    }

    public function completeAction(Request $request, Response $response, array $args)
    {
        $project = Project::findOrFail($args['id']);

        $project->done_at = Carbon::now();

        $project->save();

        return $response->withRedirect($this->router->pathFor('project.show', [
            'id' => $args['id'],
        ]));
    }

    public function reopenAction(Request $request, Response $response, array $args)
    {
        $project = Project::findOrFail($args['id']);

        $project->done_at = null;

        $project->save();

        return $response->withRedirect($this->router->pathFor('project.show', [
            'id' => $args['id'],
        ]));
    }

    public function saveAction(Request $request, Response $response, array $args)
    {
        $rules = [
            'user' => v::notEmpty()->intVal(),
            'name' => v::notEmpty(),
            'color' => v::optional(v::hexRgbColor()),
            'tags' => v::notEmpty(),
            'description' => v::notEmpty(),
            'client' => v::notEmpty()->intVal(),
            'contact' => v::notEmpty(),
            'started_at' => v::optional(v::date()),
            'due_at' => v::optional(v::min($request->getParam('started_at'))->date()),
            'issued_at' => v::optional(v::date()),
        ];

        $validation = $this->validator->validate($request, $rules);

        if ($validation->failed()) {
            $this->flash->addMessage('danger', 'Tu proyecto no ha sido guardado, revisa los errores en el formulario.');

            return $response->withRedirect($this->router->pathFor('project.new'));
        }

        $project = Project::create([
            'user_id' => filter_var($request->getParam('user'), FILTER_SANITIZE_NUMBER_INT),
            'name' => filter_var($request->getParam('name'), FILTER_SANITIZE_STRING),
            'description' => $request->getParam('description'),
            'color' => $request->getParam('color') ? filter_var($request->getParam('color'), FILTER_SANITIZE_STRING) : $this->colorPicker(),
            'client_id' => filter_var($request->getParam('client'), FILTER_SANITIZE_NUMBER_INT),
            'contact' => filter_var($request->getParam('contact'), FILTER_SANITIZE_STRING),
            'started_at' => $request->getParam('started_at') ? Carbon::createFromFormat('Y-m-d H:i', $request->getParam('started_at')) : null,
            'due_at' => $request->getParam('due_at') ? Carbon::createFromFormat('Y-m-d H:i', $request->getParam('due_at')) : null,
            'budget' => filter_var($request->getParam('budget'), FILTER_SANITIZE_STRING),
            'bill' => $request->getParam('bill') ? filter_var($request->getParam('bill'), FILTER_SANITIZE_STRING) : null,
            'issued_at' => $request->getParam('issued_at') ? Carbon::createFromFormat('Y-m-d H:i', $request->getParam('issued_at')) : null,
            'bill_comment' => $request->getParam('bill_comment')
        ]);

        if (!empty($request->getParam('staff'))) {
            $project->users()->sync($request->getParam('staff'));
        }

        if (!empty($request->getParam('tags'))) {
            $project->tags()->sync($request->getParam('tags'));
        }

        if ($this->auth->getUserId() != $request->getParam('user')) {
            $project->notifications()->create([
                'user_id' => $request->getParam('user'),
                'description' => 'Nuevo proyecto',
            ]);
        }

        foreach ($request->getParam('staff') as $id) {
            if ($this->auth->getUserId() != $id) {
                $project->notifications()->create([
                    'user_id' => $id,
                    'description' => 'Nuevo proyecto asignado',
                ]);
            }
        }

        return $response->withRedirect($this->router->pathFor('project.show', [
            'id' => $project->project_id,
        ]));
    }

    public function modifyAction(Request $request, Response $response, array $args)
    {
        $rules = [
            'user' => v::notEmpty()->intVal(),
            'name' => v::notEmpty(),
            'color' => v::notEmpty()->hexRgbColor(),
            'tags' => v::notEmpty(),
            'description' => v::notEmpty(),
            'client' => v::notEmpty()->intVal(),
            'contact' => v::notEmpty(),
            'started_at' => v::notEmpty()->date(),
            'due_at' => v::notEmpty()->min($request->getParam('started_at'))->date(),
            'issued_at' => v::optional(v::date()),
        ];

        $validation = $this->validator->validate($request, $rules);

        if ($validation->failed()) {
            $this->flash->addMessage('danger', 'Tu proyecto no ha sido guardado, revisa los errores en el formulario.');

            return $response->withRedirect($this->router->pathFor('project.edit', [
                'id' => $args['id'],
            ]));
        }

        $project = Project::findOrFail($args['id']);

        $project->user_id = filter_var($request->getParam('user'), FILTER_SANITIZE_NUMBER_INT);
        $project->name = filter_var($request->getParam('name'), FILTER_SANITIZE_STRING);
        $project->color = filter_var($request->getParam('color'), FILTER_SANITIZE_STRING);
        $project->description = $request->getParam('description');
        $project->client_id = filter_var($request->getParam('client'), FILTER_SANITIZE_NUMBER_INT);
        $project->started_at = $request->getParam('started_at') ? Carbon::createFromFormat('Y-m-d H:i', $request->getParam('started_at')) : null;
        $project->due_at = $request->getParam('due_at') ? Carbon::createFromFormat('Y-m-d H:i', $request->getParam('due_at')) : null;
        $project->budget = filter_var($request->getParam('budget'), FILTER_SANITIZE_STRING);
        $project->bill = $request->getParam('bill') ? filter_var($request->getParam('bill'), FILTER_SANITIZE_STRING) : null;
        $project->issued_at = $request->getParam('issued_at') ? Carbon::createFromFormat('Y-m-d H:i', $request->getParam('issued_at')) : null;
        $project->bill_comment = $request->getParam('bill_comment');

        $project->save();

        if (!empty($request->getParam('staff'))) {
            $project->users()->sync($request->getParam('staff'));
        }

        if (!empty($request->getParam('tags'))) {
            $project->tags()->sync($request->getParam('tags'));
        }

        return $response->withRedirect($this->router->pathFor('project.show', [
            'id' => $project->project_id,
        ]));
    }

    public function deleteAction(Request $request, Response $response, array $args)
    {
        $project = Project::findOrFail($args['id']);

        $project->tasks()->delete();
        $project->notifications()->delete();

        $project->delete();

        return $response->withRedirect($this->router->pathFor('project.inprogress'));
    }

    private function colorPicker()
    {
        $colors = [
            '#ff0000',
            '#ff4000',
            '#ff8000',
            '#ffbf00',
            '#cccc00',
            '#bfff00',
            '#80ff00',
            '#40ff00',
            '#00ff00',
            '#00ff40',
            '#00ff80',
            '#00ffbf',
            '#00ffff',
            '#00bfff',
            '#0080ff',
            '#0040ff',
            '#0000ff',
            '#4000ff',
            '#8000ff',
            '#bf00ff',
            '#ff00ff',
            '#ff00bf',
            '#ff0080',
            '#ff0040',
            '#ff0000',
        ];

        $color_id = mt_rand(1, 24);
        return $colors[$color_id];
    }
}
