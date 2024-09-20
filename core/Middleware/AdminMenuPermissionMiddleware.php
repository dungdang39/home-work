<?php

namespace Core\Middleware;

use App\Admin\Service\PermissionService;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

/**
 * 관리자 메뉴 권한 체크 미들웨어
 */
class AdminMenuPermissionMiddleware
{
    private PermissionService $service;

    public function __construct(
        PermissionService $service
    ) {
        $this->service = $service;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $config = $request->getAttribute('config');
        $member = $request->getAttribute('login_member');
        $mb_id = isset($member['mb_id']) ? $member['mb_id'] : '';

        $response = $handler->handle($request);

        if (is_super_admin($config, $mb_id)) {
            return $response;
        }

        $route_context = RouteContext::fromRequest($request);
        $current_route = $route_context->getRoute()->getName();
        $method = $request->getMethod();
        $permission = $this->service->existsAdminMenuPermission($mb_id, $current_route, $method);

        if (!$permission) {
            throw new Exception('접근 권한이 없습니다.');
        }

        return $handler->handle($request);
    }
}