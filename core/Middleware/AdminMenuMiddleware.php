<?php

namespace Core\Middleware;

use App\Base\Service\AdminMenuService;
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
        $current_route = $route_context->getRoute()->getName();

        $fetch_menus = $this->menu_service->fetchAll();

        $admin_menus = [];
        foreach ($fetch_menus as &$menu) {
            $route_name = $menu['am_route'];
            // url 변환
            $menu['url'] = $this->generateUrl($request, $route_name);
            // 메뉴 활성화
            $menu['active'] = $this->isActiveMenu($current_route, $route_name);
        }
        $admin_menus = $this->buildMenuTree($fetch_menus);

        // 대시보드는 관리자메뉴에 없으므로 별도로 활성화 처리
        if ($current_route === "admin.dashboard") {
            $admin_menus[key($admin_menus)]['active'] = true;
        }

        $view = Twig::fromRequest($request);
        $view->getEnvironment()->addGlobal('admin_menus', $admin_menus);

        return $handler->handle($request);
    }

    /**
     * URL 생성
     * @param Request $request
     * @param string|null $route_name
     * @return string|null
     */
    private function generateUrl(Request $request, ?string $menu_route): ?string
    {
        if ($menu_route === null) {
            return null;
        }

        try {
            return RouteContext::fromRequest($request)
                ->getRouteParser()
                ->urlFor($menu_route);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * 메뉴 활성화 여부
     * @param string $current_route
     * @param string $menu_route
     * @return bool
     */
    private function isActiveMenu(string $current_route, ?string $menu_route): bool
    {
        if ($menu_route === null) {
            return false;
        }

        if ($menu_route === $current_route) {
            return true;
        }

        $pattern = $menu_route . ".*";
        $pattern = preg_quote($pattern, '#');
        $pattern = str_replace('\*', '.*', $pattern);

        if (preg_match('#^' . $pattern . '\z#u', $current_route) === 1) {
            return true;
        }

        return false;
    }

    /**
     * 재귀적으로 메뉴 트리 생성
     * @param array $menus
     * @param int|null $parent_id
     * @return array
     */
    private function buildMenuTree(array $menus, int $parent_id = null) {
        $branch = [];

        foreach ($menus as $menu) {
            if ($menu['am_parent_id'] === $parent_id) {
                $children = $this->buildMenuTree($menus, $menu['am_id']);
                if ($children) {
                    $menu['children'] = $children;
                }
                $branch[] = $menu;
            }
        }

        return $branch;
    }
}
