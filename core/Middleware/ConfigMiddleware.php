<?php

namespace Core\Middleware;

use App\Config\ConfigService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Exception;

/**
 * Config Middleware
 */
class ConfigMiddleware
{
    private ConfigService $config_service;

    public function __construct(ConfigService $config_service)
    {
        $this->config_service = $config_service;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $config = $this->config_service->getConfig();

        if (!$config) {
            throw new Exception('Config not found.');
        }

        $request = $request->withAttribute('config', $config);

        return $handler->handle($request);
    }
}
