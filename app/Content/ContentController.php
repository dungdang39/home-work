<?php

namespace App\Content;

use App\Content\Model\ContentCreateRequest;
use App\Content\Model\ContentSearchRequest;
use App\Content\ContentService;
use App\Content\Model\ContentUpdateRequest;
use Core\BaseController;
use DI\Container;
use Exception;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
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
    public function index(Request $request, Response $response, ContentSearchRequest $search): Response {
        // 검색 조건 설정
        $params = $search->publics();

        // 총 데이터 수 조회 및 페이징 정보 설정
        $total_count = $this->service->fetchContentsTotalCount($params);
        $search->setTotalCount($total_count);
        
        // 컨텐츠 목록 조회
        $contents = $this->service->getContents($params);

        $response_data = [
            "contents" => $contents,
            "total_count" => $total_count,
            "search" => $search,
            "pagination" => $search->getPaginationInfo(),
            "query_params" => $request->getQueryParams(),
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
    public function insert(Request $request, Response $response, ContentCreateRequest $data): Response
    {
        try {
            // 파일 업로드 처리
            $this->service->makeContentDir($request);
            $this->service->uploadImage($request, $data);
            // 파일 필드 제거
            unset($data->head_image_file);
            unset($data->foot_image_file);

            $this->service->insert($data->publics());
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
    public function update(Request $request, Response $response, string $code, ContentUpdateRequest $data): Response
    {
        try {
            $content = $this->service->getContent($code);

            // @todo: 기존 이미지 파일 삭제 처리
            $this->service->makeContentDir($request);
            $this->service->uploadImage($request, $data);
            // 파일 필드 제거
            unset($data->head_image_file);
            unset($data->foot_image_file);

            $this->service->update($content['code'], $data->publics());
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        return $this->redirectRoute($request, $response, 'admin.content.manage.view', ['code' => $content['code']]);
    }

    /**
     * 컨텐츠 삭제
     */
    public function delete(Response $response, string $code): Response
    {
        $content = $this->service->getContent($code);

        // @todo: 이미지 파일 삭제 처리
        $this->service->delete($content['code']);

        return $response->withJson(['message' => '컨텐츠가 삭제되었습니다.']);
    }
}
