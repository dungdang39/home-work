<?php

namespace Core\Middleware;

use App\Config\ConfigService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Views\Twig;

/**
 *  Middleware
 */
class ThemeMiddleware
{
    private ConfigService $config_service;

    public function __construct(ConfigService $config_service)
    {
        $this->config_service = $config_service;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $config = $this->config_service->getConfig();
        $theme_name = $config['cf_theme'] ?: 'basic';
        $theme_path = "/theme/{$theme_name}";

        $view = Twig::fromRequest($request);
        $view->getEnvironment()->addGlobal('config', $config);
        $view->getEnvironment()->addGlobal('theme_path', $theme_path);
        $request = $request->withAttribute('theme_path', $theme_path);

        return $handler->handle($request);
    }
}
