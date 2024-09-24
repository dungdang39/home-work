<?php

namespace App\Banner\Controller;

use App\Banner\BannerService;
use App\Banner\Model\BannerCreateRequest;
use App\Banner\Model\BannerSearchRequest;
use App\Banner\Model\BannerUpdateRequest;
use Core\BaseController;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class BannerController extends BaseController
{
    private BannerService $service;

    public function __construct(
        BannerService $service,
    ) {
        $this->service = $service;
    }

    /**
     * 배너 목록 페이지
     */
    public function index(Request $request, Response $response, BannerSearchRequest $search_request): Response
    {
        $grouped_banners = $this->service->getGroupedBanners($search_request->publics());

        $response_data = [
            "grouped_banners" => $grouped_banners,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/banner_list.html', $response_data);
    }

    /**
     * 배너 등록 폼 페이지
     */
    public function create(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/banner_form.html');
    }

    /**
     * 배너 등록
     */
    public function insert(Request $request, Response $response, BannerCreateRequest $data): Response
    {
        $this->service->uploadImage($request, $data);
        $this->service->insert($data->publics());

        return $this->redirectRoute($request, $response, 'admin.design.banner');
    }

    /**
     * 배너 상세 폼 페이지
     */
    public function view(Request $request, Response $response, string $bn_id): Response
    {
        $banner = $this->service->getBanner($bn_id);
        $banner['bn_image_width'] = $this->service->getImageWidth($request, $banner['bn_image']);
        $banner['bn_mobile_image_width'] = $this->service->getImageWidth($request, $banner['bn_image']);

        $response_data = [
            "banner" => $banner,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/banner_form.html', $response_data);
    }

    /**
     * 배너 수정
     */
    public function update(Request $request, Response $response, BannerUpdateRequest $data, string $bn_id): Response
    {
        $banner = $this->service->getBanner($bn_id);

        if ($data->bn_image_del) {
            $this->service->deleteImage($request, $banner['bn_image']);
            $data->bn_image = null;
            unset($data->bn_image_del);
        }
        if ($data->bn_mobile_image_del) {
            $this->service->deleteImage($request, $banner['bn_mobile_image']);
            $data->bn_mobile_image = null;
            unset($data->bn_mobile_image_del);
        }

        $this->service->uploadImage($request, $data);
        $this->service->update($banner['bn_id'], $data->publics());

        return $this->redirectRoute($request, $response, 'admin.design.banner.view', ['bn_id' => $banner['bn_id']]);
    }

    /**
     * 배너 삭제
     */
    public function delete(Request $request, Response $response, string $bn_id): Response
    {
        $banner = $this->service->getBanner($bn_id);

        $this->service->deleteImage($request, $banner['bn_image']);
        $this->service->deleteImage($request, $banner['bn_mobile_image']);
        $this->service->delete($banner['bn_id']);

        return $response->withJson(['message' => '배너가 삭제되었습니다.']);
    }

    /**
     * 배너 전시순서 변경
     */
    public function update_order(Request $request, Response $response): Response
    {
        // $request_body = $request->getParsedBody();
        // $this->service->updateOrder($request_body);

        return $this->redirectRoute($request, $response, 'admin.design.banner');
    }
}
