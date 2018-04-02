<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use App\Auth\AuraAuth;
use Slim\Interfaces\RouterInterface;
use Carbon\Carbon;

use App\Entities\Notification;

/**
 *
 */
class NotificationController
{
    private $auth;
    private $logger;
    private $router;
    private $view;

    public function __construct(Twig $view, LoggerInterface $logger, AuraAuth $auth, RouterInterface $router)
    {
        $this->auth = $auth;
        $this->logger = $logger;
        $this->router = $router;
        $this->view = $view;
    }

    public function allAction(Request $request, Response $response, array $args)
    {
        $notifications = Notification::where('user_id', $this->auth->getUserId())
            ->where('is_readed', 0)
            ->orderBy('created_at', 'ASC')
            ->get();

        $notify = null;

        foreach ($notifications as $notification) {
            if (!$notification->showed_at) {
                $notification->showed_at = Carbon::now();

                $notification->save();

                $notify .= "notifyMe('{$notification->description}')";
            }
        }

        $newResponse = $response->withHeader('X-IC-Script', $notify);

        return $this->view->render($newResponse, 'notification/all.twig', [
            'notifications' => $notifications
        ]);
    }

    public function emptyAction(Request $request, Response $response, array $args)
    {
        $notifications = Notification::where('user_id', $this->auth->getUserId())
            ->where('is_readed', 0)
            ->get();

        foreach ($notifications as $notification) {
            $notification->is_readed = 1;

            $notification->save();
        }

        $route = $notification->notificable_type === 'App\Entities\Task' ? 'task.show' : 'project.show';

        return $response->withRedirect($request->getHeader('HTTP_REFERER')[0]);
    }

    public function showAction(Request $request, Response $response, array $args)
    {
        $notification = Notification::findOrFail($args['id']);

        $notification->is_readed = 1;

        $notification->save();

        $route = $notification->notificable_type === 'App\Entities\Task' ? 'task.show' : 'project.show';

        return $response->withRedirect($this->router->pathFor($route, [
            'id' => $notification->notificable_id
        ]));
    }
}
