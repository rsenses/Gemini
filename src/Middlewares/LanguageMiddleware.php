<?php

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Auth\AuraAuth;
use Slim\Flash\Messages;
use Negotiation\LanguageNegotiator;
use Slim\Interfaces\RouterInterface;
use Slim\Collection;
use Slim\Views\Twig;

class LanguageMiddleware
{
    private $auth;
    private $flash;
    private $negotiator;
    private $router;
    private $settings;
    private $view;

    public function __construct(AuraAuth $auth, Messages $flash, LanguageNegotiator $negotiator, RouterInterface $router, Collection $settings, Twig $view)
    {
        $this->auth = $auth;
        $this->flash = $flash;
        $this->negotiator = $negotiator;
        $this->router = $router;
        $this->settings = $settings;
        $this->view = $view;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        if ($request->getUri()->getPath() === '/') {
            $bestLanguage = $this->negotiator->getBest($request->getHeaderLine('Accept-Language'), $this->settings['allowedLocales']);

            $lang = $bestLanguage ? $bestLanguage->getType() : $this->settings['allowedLocales'][0];

            $this->view->getEnvironment()->addGlobal('lang', $lang);

            return $response->withRedirect($this->router->pathFor('home', [
                'lang' => $lang
            ]));
        } else {
            if ($request->getAttribute('route')) {
                $lang = $request->getAttribute('route')->getArgument('lang');

                if (!in_array($lang, $this->settings['allowedLocales'])) {
                    $lang = $this->settings['allowedLocales'][0];

                    $this->view->getEnvironment()->addGlobal('lang', $lang);

                    return $response->withRedirect($this->router->pathFor('home', [
                        'lang' => $lang
                    ]));
                } else {
                    $this->view->getEnvironment()->addGlobal('lang', $lang);
                }
            } else {
                $lang = $this->settings['allowedLocales'][0];

                $this->view->getEnvironment()->addGlobal('lang', $lang);
            }
        }

        $this->settings['lang'] = $lang;

        date_default_timezone_set('Europe/Madrid');
        putenv("LANG=$lang");
        setlocale(LC_ALL, $this->settings['locales'][$lang]);

        return $next($request, $response);
    }
}
