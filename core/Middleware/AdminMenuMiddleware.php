<?php

namespace Core\Middleware;

use App\Admin\Service\AdminMenuService;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

/**
 * 관리자 메뉴 미들웨어
 * - 관리자 메뉴를 부모/자식 배열로 만들어 템플릿 전역변수로 설정
 */
class AdminMenuMiddleware
{
    private AdminMenuService $menu_service;

    public function __construct(
        AdminMenuService $menu_service
    ) {
        $this->menu_service = $menu_service;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $route_context = RouteContext::fromRequest($request);
        $admin_route = $this->get_admin_route_name($route_context);

        $fetch_menus = $this->menu_service->fetchAll();

        $admin_menu = [];
        foreach ($fetch_menus as $menu) {
            // 메뉴 활성화 여부
            $menu['active'] = ($menu['am_route'] === $admin_route);
            // url 변환
            $menu['url'] = $this->generateUrl($route_context, $menu['am_route']);
            // 메뉴 분류
            if ($menu['am_parent_id'] === null) {
                $admin_menu[$menu['am_id']] = $menu;
            } else {
                if ($menu['active']) {
                    $admin_menu[$menu['am_parent_id']]['active'] = true;
                }
                $admin_menu[$menu['am_parent_id']]['children'][$menu['am_order']] = $menu;
            }
        }

        // 대시보드는 관리자메뉴에 없으므로 별도로 활성화 처리
        if ($admin_route === "dashboard") {
            $admin_menu[key($admin_menu)]['active'] = true;
        }
        $request = $request->withAttribute('admin_menu', $admin_menu);
        $request = $request->withAttribute('admin_route', $admin_route);

        $view = Twig::fromRequest($request);
        $view->getEnvironment()->addGlobal('admin_menu', $admin_menu);

        return $handler->handle($request);
    }

    /**
     * URL 생성
     * @param RouteContext $routeContext
     * @param string|null $routeName
     * @return string|null
     */
    private function generateUrl(RouteContext $route_context, ?string $route_name): ?string
    {
        if ($route_name === null) {
            return null;
        }

        try {
            return $route_context->getRouteParser()->urlFor($route_name);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * 관리자 라우트 추출
     * 
     * 관리자 메뉴의 route는 admin.{name}.{action} 형식이므로
     * admin.{name}을 추출하여 반환
     * 
     * @param RouteContext $route_context
     * @return string
     */
    private function get_admin_route_name(RouteContext $route_context): string
    {
        try {
            $route_fullname = $route_context->getRoute()->getName();
            $array = explode('.', $route_fullname);
            return $array[0] . '.' . $array[1];
        } catch (Exception $e) {
            return '';
        }
    }
}
