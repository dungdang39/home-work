<?php

namespace App\Base\Controller\Admin;

use App\Base\Model\Admin\ContentCreateRequest;
use App\Base\Model\Admin\ContentSearchRequest;
use App\Base\Service\ContentService;
use App\Base\Model\Admin\ContentUpdateRequest;
use Core\BaseController;
use Core\FileService;
use Core\Validator\Validator;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class ContentController extends BaseController
{
    private ContentService $service;
    private FileService $file_service;

    public function __construct(
        ContentService $service,
        FileService $file_service
    ) {
        $this->service = $service;
        $this->file_service = $file_service;
    }

    /**
     * 컨텐츠 목록 페이지
     */
    public function index(Request $request, Response $response, ContentSearchRequest $search): Response {
        // 검색 조건 설정
        $params = $search->publics();

        // 총 데이터 수 조회 및 페이징 정보 설정
        $total_count = $this->service->fetchContentsCount($params);
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
        return $view->render($response, '@admin/content/list.html', $response_data);
    }

    /**
     * 컨텐츠 등록 폼 페이지
     */
    public function create(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/content/form.html');
    }

    /**
     * 컨텐츠 등록
     */
    public function insert(Request $request, Response $response, ContentCreateRequest $data): Response
    {
        $folder = $this->service::DIRECTORY;
        $data->head_image = $this->file_service->upload($request, $folder, $data->head_image_file) ?: null;
        $data->foot_image = $this->file_service->upload($request, $folder, $data->foot_image_file) ?: null;

        unset($data->head_image_file);
        unset($data->foot_image_file);

        $this->service->insert($data->publics());

        return $this->redirectRoute($request, $response, 'admin.content.manage');
    }

    /**
     * 컨텐츠 상세 폼 페이지
     */
    public function view(Request $request, Response $response, string $code): Response
    {
        $content = $this->service->getContent($code);

        $response_data = [
            "content" => $content,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/content/form.html', $response_data);
    }

    /**
     * 컨텐츠 수정
     */
    public function update(Request $request, Response $response, string $code, ContentUpdateRequest $data): Response
    {
        $content = $this->service->getContent($code);

        $folder = $this->service::DIRECTORY;
        if ($data->head_image_del || Validator::isUploadedFile($data->head_image_file)) {
            $this->file_service->deleteByDb($request, $content['head_image']);
            $data->head_image = $this->file_service->upload($request, $folder, $data->head_image_file);
        }
        if ($data->foot_image_del || Validator::isUploadedFile($data->foot_image_file)) {
            $this->file_service->deleteByDb($request, $content['foot_image']);
            $data->foot_image = $this->file_service->upload($request, $folder, $data->foot_image_file);
        }

        unset($data->head_image_del);
        unset($data->foot_image_del);
        unset($data->head_image_file);
        unset($data->foot_image_file);

        $this->service->update($content['code'], $data->publics());

        return $this->redirectRoute($request, $response, 'admin.content.manage.view', ['code' => $content['code']]);
    }

    /**
     * 컨텐츠 삭제
     */
    public function delete(Request $request, Response $response, string $code): Response
    {
        $content = $this->service->getContent($code);

        $this->service->deleteContent($request, $content);

        return $response->withJson(['message' => '컨텐츠가 삭제되었습니다.']);
    }
}
