<?php

namespace App\Base\Controller\Admin;

use App\Base\Service\BannerService;
use App\Base\Model\Admin\BannerCreateRequest;
use App\Base\Model\Admin\BannerSearchRequest;
use App\Base\Model\Admin\BannerUpdateRequest;
use App\Base\Service\MainPageService;
use Core\BaseController;
use Core\FileService;
use Core\ImageService;
use Core\Validator\Validator;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class BannerController extends BaseController
{
    private BannerService $service;
    private FileService $file_service;
    private ImageService $image_service;
    private MainPageService $mainpage_service;

    public function __construct(
        BannerService $service,
        FileService $file_service,
        ImageService $image_service,
        MainPageService $mainpage_service
    ) {
        $this->service = $service;
        $this->file_service = $file_service;
        $this->image_service = $image_service;
        $this->mainpage_service = $mainpage_service;
    }

    /**
     * 배너 목록 페이지
     */
    public function index(Request $request, Response $response, BannerSearchRequest $search): Response
    {
        // 검색 조건 설정
        $params = $search->publics();
        
        $positions = $this->mainpage_service->getBannerPositions();
        $banners = $this->service->getBanners($params);

        foreach ($positions as $key => $position) {
            foreach ($banners as $banner) {
                if ($position['id'] === $banner['bn_position']) {
                    $positions[$key]['banners'][] = $banner;
                }
            }
        }

        $response_data = [
            "positions" => $positions,
            "search" => $search,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/design/banner/list.html', $response_data);
    }

    /**
     * 배너 등록 폼 페이지
     */
    public function create(Request $request, Response $response): Response
    {
        $positions = $this->mainpage_service->getBannerPositions();

        $response_data = [
            'positions' => $positions,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/design/banner/form.html', $response_data);
    }

    /**
     * 배너 등록
     */
    public function insert(Request $request, Response $response, BannerCreateRequest $data): Response
    {
        $folder = $this->service::DIRECTORY;
        $data->bn_image = $this->file_service->upload($request, $folder, $data->bn_image_file) ?: null;
        $data->bn_mobile_image = $this->file_service->upload($request, $folder, $data->bn_mobile_image_file) ?: null;

        $this->service->insert($data->publics());

        return $this->redirectRoute($request, $response, 'admin.design.banner');
    }

    /**
     * 배너 상세 폼 페이지
     */
    public function view(Request $request, Response $response, string $bn_id): Response
    {
        $banner = $this->service->getBanner($bn_id);
        $positions = $this->mainpage_service->getBannerPositions();

        $response_data = [
            'positions' => $positions,
            'banner' => $banner,
            'image_width' => $this->image_service->getImageWidth($request, $banner->bn_image),
            'mobile_image_width' => $this->image_service->getImageWidth($request, $banner->bn_mobile_image)
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/design/banner/form.html', $response_data);
    }

    /**
     * 배너 수정
     */
    public function update(Request $request, Response $response, BannerUpdateRequest $data, string $bn_id): Response
    {
        $banner = $this->service->getBanner($bn_id);
        $banner->fill($data);

        $folder = $this->service::DIRECTORY;
        if ($data->bn_image_del || Validator::isUploadedFile($data->bn_image_file)) {
            $this->file_service->deleteByDb($request, $banner->bn_image);
            $banner->bn_image = $this->file_service->upload($request, $folder, $data->bn_image_file);
        }
        if ($data->bn_mobile_image_del || Validator::isUploadedFile($data->bn_mobile_image_file)) {
            $this->file_service->deleteByDb($request, $banner->bn_mobile_image);
            $banner->bn_mobile_image = $this->file_service->upload($request, $folder, $data->bn_mobile_image_file);
        }
        
        $this->service->update($banner->bn_id, $banner->publics());

        return $this->redirectRoute($request, $response, 'admin.design.banner.view', ['bn_id' => $banner->bn_id]);
    }

    /**
     * 배너 삭제
     */
    public function delete(Request $request, Response $response, string $bn_id): Response
    {
        $banner = $this->service->getBanner($bn_id);
        
        $this->service->deleteBanner($request, $banner);

        return $response->withJson(['message' => '배너가 삭제되었습니다.']);
    }

    /**
     * 배너 전시순서 변경
     */
    public function updateListOrder(Request $request, Response $response): Response
    {
        // $request_body = $request->getParsedBody();
        // $this->service->updateOrder($request_body);

        return $this->redirectRoute($request, $response, 'admin.design.banner');
    }

    /**
     * 배너 활성화/비활성화 전환
     */
    public function toggleEnabled(Request $request, Response $response, int $bn_id): Response
    {
        $banner = $this->service->getBanner($bn_id);
        $is_enabled = (bool)$banner->bn_is_enabled;

        $this->service->update($banner->bn_id, ['bn_is_enabled' => !$is_enabled]);

        return $response->withJson(['message' => '배너가 ' . ($is_enabled ? '비' : '') . '활성화되었습니다.']);
    }
}