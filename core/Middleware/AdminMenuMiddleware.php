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
        $routeContext = RouteContext::fromRequest($request);
        $route_group = $this->get_route_group($request);

        $fetch_menus = $this->menu_service->fetchAll();

        $admin_menu = [];
        foreach ($fetch_menus as $menu) {
            // 메뉴 활성화 여부
            $menu['active'] = ($menu['am_route'] === $route_group);

            // url 변환
            $menu['url'] = $this->generateUrl($routeContext, $menu['am_route']);

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
        if ($route_group === "dashboard") {
            $admin_menu[key($admin_menu)]['active'] = true;
        }
        $request = $request->withAttribute('admin_menu', $admin_menu);
        $request = $request->withAttribute('route_group', $route_group);

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
    private function generateUrl(RouteContext $routeContext, ?string $routeName): ?string
    {
        if ($routeName === null) {
            return null;
        }

        try {
            return $routeContext->getRouteParser()->urlFor($routeName . ".index");
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * 라우트 그룹 반환
     * @param Request $request
     * @return string
     */
    private function get_route_group(Request $request): string
    {
        $routeContext = RouteContext::fromRequest($request);
        $route_name = $routeContext->getRoute()->getName();
        $route_group_array = explode('.', $route_name);

        return $route_group_array[0];
    }
}
