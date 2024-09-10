<?php

namespace App\Content;

use App\Content\Model\ContentCreateRequest;
use App\Content\Model\ContentSearchRequest;
use App\Content\Model\ContentUpdateRequest;
use App\Content\ContentService;
use Core\BaseController;
use Core\Model\PageParameters;
use DI\Container;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class ContentController extends BaseController
{
    private ContentService $service;

    public function __construct(
        Container $container,
        ContentService $service,
    ) {
        parent::__construct($container);

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
        try {
            $request_body = $request->getParsedBody();
            $data = new ContentCreateRequest($request_body);

            $this->service->insert($data->toArray());
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        return $this->redirectRoute($request, $response, 'admin.content.manage');
    }

    /**
     * 컨텐츠 상세 폼 페이지
     */
    public function view(Request $request, Response $response, string $code): Response
    {
        try {
            $content = $this->service->getContent($code);
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        $response_data = [
            "content" => $content,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/content_form.html', $response_data);
    }

    /**
     * 컨텐츠 수정
     */
    public function update(Request $request, Response $response, string $code): Response
    {
        try {
            $content = $this->service->getContent($code);
            $request_body = $request->getParsedBody();
            $data = new ContentUpdateRequest($request_body);

            $this->service->update($content['code'], $data->toArray());
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        return $this->redirectRoute($request, $response, 'admin.content.manage.view', ['code' => $content['code']]);
    }

    /**
     * 컨텐츠 삭제
     */
    public function delete(Request $request, Response $response, string $code): Response
    {
        try {
            $content = $this->service->getContent($code);

            $this->service->delete($content['code']);
        } catch (\Exception $e) {
            return $this->responseJson($response, $e->getMessage(), $e->getCode());
        }

        return $this->responseJson($response, '컨텐츠가 삭제되었습니다.');
    }
}
