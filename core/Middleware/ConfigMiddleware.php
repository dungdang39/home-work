<?php

namespace Core\Middleware;

use App\Base\Service\ConfigService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Exception;

/**
 * Config Middleware
 */
class ConfigMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $config = ConfigService::getConfig();

        if (!$config) {
            throw new Exception('Config not found.', 400);
        }

        $request = $request->withAttribute('config', $config);

        return $handler->handle($request);
    }
}
