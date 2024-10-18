<?php

namespace Core\Middleware;

use App\Base\Service\ThemeService;
use App\Base\Service\ConfigService;
use Core\AppConfig;
use Core\Lib\UriHelper;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Views\Twig;

/**
 *  템플릿 설정 Middleware
 */
class TemplateMiddleware
{
    private ConfigService $config_service;
    private UriHelper $uri_helper;

    public function __construct(
        ConfigService $config_service,
        UriHelper $uri_helper
    ) {
        $this->config_service = $config_service;
        $this->uri_helper = $uri_helper;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $base_url = $this->uri_helper->getBaseUrl($request);
        $theme = $this->config_service->getTheme();
        $theme_path = '/' . ThemeService::DIRECTORY . '/' . $theme;

        // 템플릿 전역변수 설정
        $view = Twig::fromRequest($request);
        $view->getEnvironment()->addGlobal('app_config', AppConfig::getInstance());
        $view->getEnvironment()->addGlobal('base_url', $base_url);
        $view->getEnvironment()->addGlobal('theme_url', $base_url . $theme_path);

        return $handler->handle($request);
    }
}
