<?php

namespace Core\Middleware;

use App\Admin\AdminMenuService;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

/**
 * 관리자 메뉴 권한 체크 미들웨어
 */
class AdminMenuAuthMiddleware
{
    private AdminMenuService $menu_service;

    public function __construct(
        AdminMenuService $menu_service
    ) {
        $this->menu_service = $menu_service;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $admin_menu = $request->getAttribute('admin_menu');

        return $handler->handle($request);
    }
}