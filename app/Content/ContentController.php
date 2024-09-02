<?php

namespace App\Content;

use App\Content\Model\ContentCreateRequest;
use App\Content\Model\ContentSearchRequest;
use App\Content\Model\ContentUpdateRequest;
use App\Content\ContentService;
use Core\Model\PageParameters;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

class ContentController
{
    private ContentService $service;

    public function __construct(
        ContentService $service,
    ) {
        $this->service = $service;
    }

    /**
     * 컨텐츠 목록 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $query_params = $request->getQueryParams();
        $request_params = new ContentSearchRequest($query_params);
        $page_params = new PageParameters($query_params);
        $params = array_merge($request_params->toArray(), $page_params->toArray());

        $contents = $this->service->getContents($params);

        $response_data = [
            "contents" => $contents,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/content_list.html', $response_data);
    }

    /**
     * 컨텐츠 등록 폼 페이지
     */
    public function create(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/content_form.html');
    }

    /**
     * 컨텐츠 등록
     */
    public function insert(Request $request, Response $response): Response
    {
        $request_body = $request->getParsedBody();
        $data = new ContentCreateRequest($request_body);

        $this->service->insert($data->toArray());

        $routeContext = RouteContext::fromRequest($request);
        $redirect_url = $routeContext->getRouteParser()->urlFor('admin.content.manage');
        return $response->withHeader('Location', $redirect_url)->withStatus(302);
    }

    /**
     * 컨텐츠 상세 폼 페이지
     */
    public function view(Request $request, Response $response, array $args): Response
    {
        $content = $this->service->getContent($args['code']);

        $response_data = [
            "content" => $content,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/content_form.html', $response_data);
    }

    /**
     * 컨텐츠 수정
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $content = $this->service->getContent($args['code']);
        $request_body = $request->getParsedBody();
        $data = new ContentUpdateRequest($request_body);

        $this->service->update($content['code'], $data->toArray());

        $routeContext = RouteContext::fromRequest($request);
        $redirect_url = $routeContext->getRouteParser()->urlFor('admin.content.manage.view', ['code' => $content['code']]);
        return $response->withHeader('Location', $redirect_url)->withStatus(302);
    }

    /**
     * 컨텐츠 삭제
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $content = $this->service->getContent($args['code']);

            $this->service->delete($content['code']);

            return api_response_json($response, [
                'result' => 'success',
                'message' => '컨텐츠가 삭제되었습니다.',
            ], 200);
        } catch (\Exception $e) {
            return api_response_json($response, [
                'result' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}
