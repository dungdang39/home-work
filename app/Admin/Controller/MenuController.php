<?php

namespace App\Admin\Controller;

use App\Content\ContentService;
use App\Admin\Service\MenuService;
use Core\BaseController;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

/**
 * 메뉴 설정
 */
class MenuController extends BaseController
{
    private ContentService $content_service;
    private MenuService $service;

    public function __construct(
        ContentService $content_service,
        MenuService $service
    ) {
        $this->content_service = $content_service;
        $this->service = $service;
    }

    /**
     * 메뉴 설정 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $menus = $this->service->getMenus();

        $response_data = [
            'menus' => $menus,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/design/menu_form.html', $response_data);
    }

    /**
     * 메뉴 추가 폼 > 구분 별 URL 반환
     */
    public function getUrls(Request $request, Response $response, string $type): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRouteParser();

        $data = [];
        switch ($type) {
            case 'shop_category':
                // $data = $this->service->getUrls('admin');
                break;
            case 'community_group':
                // $data = $this->service->getUrls('member');
                break;
            case 'community_board':
                // $data = $this->service->getUrls('member');
                break;
            case 'content':
                $contents = $this->content_service->fetchContents();
                foreach ($contents as $content) {
                    $data[] = array(
                        'url' => $route->urlFor('content', ['code' => $content['code']]),
                        'id' => $content['code'],
                        'title' => $content['title'],
                    );
                }
                break;
            default:
                break;
        }

        return $response->withJson($data, 200);
    }

    /**
     * 메뉴 일괄수정
     */
    public function updateList(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        foreach ($data['me_id'] as $key => $value) {
            if (empty($value)) {
                $this->service->createMenu($data, $key);
            } else {
                $this->service->updateMenu($data, $key);
            }
        }

        return $this->redirectRoute($request, $response, 'admin.design.menu');
    }

    /**
     * 메뉴 삭제
     */
    public function delete(Request $request, Response $response, string $me_id): Response
    {
        $menu = $this->service->getMenu($me_id);

        $this->service->deleteMenu($menu);

        return $response->withJson(['message' => '메뉴가 삭제되었습니다.']);
    }
}
