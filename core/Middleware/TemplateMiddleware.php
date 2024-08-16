<?php

namespace Core\Middleware;

use App\Config\ConfigService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

/**
 *  Middleware
 */
class TemplateMiddleware
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
        $base_url = $this->base_url($request);

        // Twig 전역변수 설정
        $view = Twig::fromRequest($request);
        $view->getEnvironment()->addGlobal('config', $config);
        $view->getEnvironment()->addGlobal('base_url', $base_url);
        $view->getEnvironment()->addGlobal('theme_url', $base_url . $theme_path);
        $view->getEnvironment()->addGlobal('theme_path', $theme_path);
        // Request 전역변수 설정
        $request = $request->withAttribute('theme_path', $theme_path);

        return $handler->handle($request);
    }

    /**
     * 현재 URL 반환
     * @param Request $request
     * @return string
     */
    public function base_url(Request $request): string
    {
        $routeContext = RouteContext::fromRequest($request);
        return $this->default_url($request) . rtrim($routeContext->getBasePath(), '/');
    }

    /**
     * Base URL 반환
     * @param Request $request
     * @return string
     */
    private function default_url(Request $request): string
    {
        $scheme = $request->getUri()->getScheme();
        $host = $request->getUri()->getHost();
        $port = $request->getUri()->getPort();
        $user = $request->getUri()->getUserInfo();

        return "{$scheme}://{$host}{$port}{$user}";
    }
}
