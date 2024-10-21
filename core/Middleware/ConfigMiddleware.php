<?php

namespace Core\Middleware;

use App\Base\Service\ConfigService;
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

    public function __construct(
        ConfigService $service
    ) {
        $this->service = $service;        
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $configs = $this->service->getConfigs();

        // 템플릿 및 라우터에 전역변수 설정
        $view = Twig::fromRequest($request);
        $view->getEnvironment()->addGlobal('configs', $configs);
        $request = $request->withAttribute('configs', $configs);

        return $handler->handle($request);
    }
}
