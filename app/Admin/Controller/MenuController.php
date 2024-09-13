<?php

namespace App\Admin\Controller;

use App\Content\ContentService;
use App\Admin\Service\MenuService;
use Core\BaseController;
use DI\Container;
use Exception;
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
        Container $container,
        ContentService $content_service,
        MenuService $service
    ) {
        parent::__construct($container);

        $this->content_service = $content_service;
        $this->service = $service;
    }

    /**
     * 메뉴 설정 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $menus = $this->service->getMenu();

        $response_data = [
            'menus' => $menus,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/menu_form.html', $response_data);
    }

    /**
     * 
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

    public function updateList(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            
            
            $this->service->updateList($data);
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        return $this->redirectRoute($request, $response, 'admin.design.menu');
    }
}
