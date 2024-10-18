<?php

namespace Core\Middleware;

use App\Base\Service\ConfigService;
use App\Base\Service\PermissionService;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpForbiddenException;
use Slim\Routing\RouteContext;

/**
 * 관리자 메뉴 권한 체크 미들웨어
 */
class AdminMenuPermissionMiddleware
{
    private ConfigService $config_service;
    private PermissionService $service;

    public function __construct(
        ConfigService $config_service,
        PermissionService $service
    ) {
        $this->config_service = $config_service;
        $this->service = $service;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $member = $request->getAttribute('login_member');
        $mb_id = isset($member['mb_id']) ? $member['mb_id'] : '';

        if ($this->config_service->isSuperAdmin($mb_id)) {
            return $handler->handle($request);
        }

        $route_context = RouteContext::fromRequest($request);
        $current_route = $route_context->getRoute()->getName();
        $method = $request->getMethod();
        $permission = $this->service->existsAdminMenuPermission($mb_id, $current_route, $method);

        if (!$permission) {
            throw new HttpForbiddenException($request, '접근 권한이 없습니다.');
        }

        return $handler->handle($request);
    }
}