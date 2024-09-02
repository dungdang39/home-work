<?php

namespace App\Admin\Controller;

use App\Content\ContentService;
use App\Admin\Service\MenuService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

/**
 * 운영진 설정
 */
class MenuController
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

    public function index(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);

        $menus = $this->service->getMenu();

        $response_data = [
            'menus' => $menus,
        ];
        return $view->render($response, '/admin/menu_form.html', $response_data);
    }

    public function getUrls(Request $request, Response $response, array $args): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRouteParser();

        $data = [];
        switch ($args['type']) {
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
                        'url' => $route->urlFor('admin.content', ['co_id' => $content['co_id']]),
                        'id' => $content['co_id'],
                        'subject' => $content['co_subject'],
                    );
                }
                break;
            default:
                break;
        }

        // json으로 반환
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        $response->getBody()->write($json);
        return $response->withStatus(200)->withAddedHeader('Content-Type', 'application/json');

    }
}
