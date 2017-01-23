<?php

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use Slim\Interfaces\RouterInterface;
use Slim\Collection;

class BreadCrumbsMiddleware
{
    private $view;
    private $router;
    private $settings;

    public function __construct(Twig $view, RouterInterface $router, Collection $settings)
    {
        $this->view = $view;
        $this->router = $router;
        $this->settings = $settings;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        $breadcrumbs = [
            'current' => 'Escritorio',
            'routes' => [[
                'name' => 'Escritorio',
                'route' => 'dashboard',
                'url' => $this->getUrl($request, 'dashboard')
            ]]
        ];

        //GET THE NAME OF CURRENT SELECTED PAGE
        $routeName = $request->getAttribute('route')->getName();

        //BUILD breadcrumbs
        switch($routeName) {
            case 'auth.change.password':
                $breadcrumbs['current'] = 'Cambiar Contraseña';
                $breadcrumbs['routes'][] = [
                    'name' => $breadcrumbs['current'],
                    'route' => $routeName,
                    'url' => $this->getUrl($request, $routeName)
                ];
                break;
            case 'auth.edit':
                $breadcrumbs['current'] = 'Mis Datos';
                $breadcrumbs['routes'][] = [
                    'name' => $breadcrumbs['current'],
                    'route' => $routeName,
                    'url' => $this->getUrl($request, $routeName)
                ];
                break;
            case 'project.all':
                $breadcrumbs['current'] = 'Todos los proyectos';
                $breadcrumbs['routes'][] = [
                    'name' => $breadcrumbs['current'],
                    'route' => $routeName,
                    'url' => $this->getUrl($request, $routeName)
                ];
                break;
            case 'project.inprogress':
                $breadcrumbs['current'] = 'Mis proyectos';
                $breadcrumbs['routes'][] = [
                    'name' => $breadcrumbs['current'],
                    'route' => $routeName,
                    'url' => $this->getUrl($request, $routeName)
                ];
                break;
            case 'project.inprogress.calendar':
                $breadcrumbs['current'] = 'Calendario';
                $breadcrumbs['routes'][] = [
                    'name' => 'Mis proyectos',
                    'route' => 'project.inprogress',
                    'url' => $this->getUrl($request, 'project.inprogress')
                ];
                $breadcrumbs['routes'][] = [
                    'name' => $breadcrumbs['current'],
                    'route' => $routeName,
                    'url' => $this->getUrl($request, $routeName)
                ];
                break;
            case 'project.completed':
                $breadcrumbs['current'] = 'Proyectos completados';
                $breadcrumbs['routes'][] = [
                    'name' => $breadcrumbs['current'],
                    'route' => $routeName,
                    'url' => $this->getUrl($request, $routeName)
                ];
                break;
            case 'project.billed':
                $breadcrumbs['current'] = 'Proyectos facturados';
                $breadcrumbs['routes'][] = [
                    'name' => $breadcrumbs['current'],
                    'route' => $routeName,
                    'url' => $this->getUrl($request, $routeName)
                ];
                break;
            case 'project.search':
                $breadcrumbs['current'] = 'Buscar proyectos';
                $breadcrumbs['routes'][] = [
                    'name' => $breadcrumbs['current'],
                    'route' => $routeName,
                    'url' => $this->getUrl($request, $routeName)
                ];
                break;
            case 'project.new':
                $breadcrumbs['current'] = 'Nuevo Proyecto';
                $breadcrumbs['routes'][] = [
                    'name' => 'Todos los proyectos',
                    'route' => 'project.all',
                    'url' => $this->getUrl($request, 'project.all')
                ];
                $breadcrumbs['routes'][] = [
                    'name' => $breadcrumbs['current'],
                    'route' => $routeName,
                    'url' => $this->getUrl($request, $routeName)
                ];
                break;
            case 'project.edit':
                $breadcrumbs['current'] = 'Editar Proyecto';
                $breadcrumbs['routes'][] = [
                    'name' => 'Todos los proyectos',
                    'route' => 'project.all',
                    'url' => $this->getUrl($request, 'project.all')
                ];
                $breadcrumbs['routes'][] = [
                    'name' => $breadcrumbs['current'],
                    'route' => $routeName,
                    'url' => $this->getUrl($request, $routeName)
                ];
                break;
            case 'project.show':
                $breadcrumbs['current'] = 'Ver Proyecto';
                $breadcrumbs['routes'][] = [
                    'name' => 'Todos los proyectos',
                    'route' => 'project.all',
                    'url' => $this->getUrl($request, 'project.all')
                ];
                $breadcrumbs['routes'][] = [
                    'name' => $breadcrumbs['current'],
                    'route' => $routeName,
                    'url' => $this->getUrl($request, $routeName)
                ];
                break;
            case 'task.inprogress':
                $breadcrumbs['current'] = 'Mis tareas';
                $breadcrumbs['routes'][] = [
                    'name' => $breadcrumbs['current'],
                    'route' => $routeName,
                    'url' => $this->getUrl($request, $routeName)
                ];
                break;
            case 'task.completed':
                $breadcrumbs['current'] = 'Completadas';
                $breadcrumbs['routes'][] = [
                    'name' => 'Mis tareas',
                    'route' => 'task.inprogress',
                    'url' => $this->getUrl($request, 'task.inprogress')
                ];
                $breadcrumbs['routes'][] = [
                    'name' => $breadcrumbs['current'],
                    'route' => $routeName,
                    'url' => $this->getUrl($request, $routeName)
                ];
                break;
            case 'task.independent':
                $breadcrumbs['current'] = 'Tareas sin proyecto';
                $breadcrumbs['routes'][] = [
                    'name' => $breadcrumbs['current'],
                    'route' => $routeName,
                    'url' => $this->getUrl($request, $routeName)
                ];
                break;
            case 'task.independent.completed':
                $breadcrumbs['current'] = 'Completadas';
                $breadcrumbs['routes'][] = [
                    'name' => 'Tareas sin proyecto',
                    'route' => 'task.independent',
                    'url' => $this->getUrl($request, 'task.independent')
                ];
                $breadcrumbs['routes'][] = [
                    'name' => $breadcrumbs['current'],
                    'route' => $routeName,
                    'url' => $this->getUrl($request, $routeName)
                ];
                break;
            case 'task.unnassigned':
                $breadcrumbs['current'] = 'Tareas sin asignar';
                $breadcrumbs['routes'][] = [
                    'name' => $breadcrumbs['current'],
                    'route' => $routeName,
                    'url' => $this->getUrl($request, $routeName)
                ];
                break;
            case 'task.assigned':
                $breadcrumbs['current'] = 'Tareas asignadas';
                $breadcrumbs['routes'][] = [
                    'name' => $breadcrumbs['current'],
                    'route' => $routeName,
                    'url' => $this->getUrl($request, $routeName)
                ];
                break;
            case 'task.new':
                $breadcrumbs['current'] = 'Nueva Tarea';
                $breadcrumbs['routes'][] = [
                    'name' => 'Mis Tareas',
                    'route' => 'task.inprogress',
                    'url' => $this->getUrl($request, 'task.inprogress')
                ];
                $breadcrumbs['routes'][] = [
                    'name' => $breadcrumbs['current'],
                    'route' => $routeName,
                    'url' => $this->getUrl($request, $routeName)
                ];
                break;
            case 'task.edit':
                $breadcrumbs['current'] = 'Editar Tarea';
                $breadcrumbs['routes'][] = [
                    'name' => 'Mis Tareas',
                    'route' => 'task.inprogress',
                    'url' => $this->getUrl($request, 'task.inprogress')
                ];
                $breadcrumbs['routes'][] = [
                    'name' => $breadcrumbs['current'],
                    'route' => $routeName,
                    'url' => $this->getUrl($request, $routeName)
                ];
                break;
            case 'task.show':
                $breadcrumbs['current'] = 'Ver Tarea';
                $breadcrumbs['routes'][] = [
                    'name' => 'Mis Tareas',
                    'route' => 'task.inprogress',
                    'url' => $this->getUrl($request, 'task.inprogress')
                ];
                $breadcrumbs['routes'][] = [
                    'name' => $breadcrumbs['current'],
                    'route' => $routeName,
                    'url' => $this->getUrl($request, $routeName)
                ];
                break;
            case 'user.all':
                $breadcrumbs['current'] = 'Staff';
                $breadcrumbs['routes'][] = [
                    'name' => $breadcrumbs['current'],
                    'route' => $routeName,
                    'url' => $this->getUrl($request, $routeName)
                ];
                break;
            case 'user.edit':
                $breadcrumbs['routes'][] = [
                    'name' => 'Staff',
                    'route' => 'user.all',
                    'url' => $this->getUrl($request, 'user.all')
                ];
                $breadcrumbs['current'] = 'Nuevo Usuario';
                $breadcrumbs['routes'][] = [
                    'name' => $breadcrumbs['current'],
                    'route' => $routeName,
                    'url' => $this->getUrl($request, $routeName)
                ];
                break;
        }

        //ALLOW VIEW TO USE IT
        $this->view->getEnvironment()->addGlobal('breadcrumbs', $breadcrumbs);
        $response = $next($request, $response);

        return $response;
    }

    private function getUrl(Request $request, $route) {
        $args = isset($request->getAttribute('routeInfo')[2]) ? $request->getAttribute('routeInfo')[2] : ['lang' => $this->settings['allowedLocales'][0]];
        return $this->router->pathFor($route, $args);
    }
}
