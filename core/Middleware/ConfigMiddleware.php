<?php

namespace Core\Middleware;

use App\Base\Service\ConfigService;
use App\Base\Service\ThemeService;
use Core\AppConfig;
use Core\Lib\UriHelper;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Views\Twig;

/**
 * Config Middleware
 */
class ConfigMiddleware
{
    private ConfigService $service;
    private UriHelper $uri_helper;

    public function __construct(
        ConfigService $service,
        UriHelper $uri_helper
    ) {
        $this->service = $service;
        $this->uri_helper = $uri_helper;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $configs = $this->service->getConfigs();
        $theme = $this->service->getTheme();
        $base_url = $this->uri_helper->getBaseUrl($request);

        ;

        // 템플릿 전역변수 설정
        $view = Twig::fromRequest($request);
        $view->getEnvironment()->addGlobal('app_config', AppConfig::getInstance());
        $view->getEnvironment()->addGlobal('configs', $configs);
        $view->getEnvironment()->addGlobal('base_url', $base_url);
        $view->getEnvironment()->addGlobal('admin_url', join('/', [$base_url, ThemeService::ADMIN_DIRECTORY]));
        $view->getEnvironment()->addGlobal('theme_url', join('/', [$base_url, ThemeService::DIRECTORY, $theme]));
        
        // Request에 설정 추가
        $request = $request->withAttribute('app_config', AppConfig::getInstance());
        $request = $request->withAttribute('configs', $configs);

        return $handler->handle($request);
    }
}
