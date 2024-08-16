<?php

namespace Core\Middleware;

use App\Admin\AdminMenuService;
use App\Admin\AdminMenuAuthService;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * 관리자 메뉴 권한 체크 미들웨어
 */
class AdminMenuAuthMiddleware
{
    private AdminMenuAuthService $auth_service;

    public function __construct(
        AdminMenuAuthService $auth_service
    ) {
        $this->auth_service = $auth_service;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $config = $request->getAttribute('config');
        $member = $request->getAttribute('member');
        $mb_id = isset($member['mb_id']) ? $member['mb_id'] : '';

        $response = $handler->handle($request);

        if (is_super_admin($config, $mb_id)) {
            return $response;
        }

        $route_group = $request->getAttribute('route_group');
        $method = $request->getMethod();
        $permission = $this->auth_service->existsAdminMenuAuth($mb_id, $route_group, $method);

        if (!$permission) {
            throw new Exception('접근 권한이 없습니다.');
        }

        return $handler->handle($request);
    }
}