<?php

namespace App\Banner\Controller;

use App\Banner\BannerService;
use App\Banner\Model\BannerCreateRequest;
use App\Banner\Model\BannerSearchRequest;
use App\Banner\Model\BannerUpdateRequest;
use Core\BaseController;
use DI\Container;
use Exception;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class BannerController extends BaseController
{
    private BannerService $service;

    public function __construct(
        Container $container,
        BannerService $service,
    ) {
        parent::__construct($container);

        $this->service = $service;
    }

    /**
     * 배너 목록 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $query_params = $request->getQueryParams();
        $params = new BannerSearchRequest($query_params);
        $grouped_banners = $this->service->getGroupedBanners($params->toArray());

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
    public function insert(Request $request, Response $response): Response
    {
        try {
            $request_body = $request->getParsedBody();
            $request_file = $request->getUploadedFiles();
            $data = new BannerCreateRequest($request_body, $request_file);

            $this->service->makeBannerDir($request);
            $this->service->uploadImage($request, $data);
            $this->service->insert($data->toArray());
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        return $this->redirectRoute($request, $response, 'admin.design.banner');
    }

    /**
     * 배너 상세 폼 페이지
     */
    public function view(Request $request, Response $response, string $bn_id): Response
    {
        try {
            $banner = $this->service->getBanner($bn_id);
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        $response_data = [
            "banner" => $banner,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/banner_form.html', $response_data);
    }

    /**
     * 배너 수정
     */
    public function update(Request $request, Response $response, string $bn_id): Response
    {
        try {
            $banner = $this->service->getBanner($bn_id);
            $request_body = $request->getParsedBody();
            $request_file = $request->getUploadedFiles();
            $data = new BannerUpdateRequest($request_body, $request_file);
    
            // @todo 기존 파일 삭제처리 필요
            $this->service->uploadImage($request, $data);
            $this->service->update($bn_id, $data->toArray());
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        return $this->redirectRoute($request, $response, 'admin.design.banner.view', ['bn_id' => $banner['bn_id']]);
    }

    /**
     * 배너 삭제
     */
    public function delete(Request $request, Response $response, string $bn_id): Response
    {
        $banner = $this->service->getBanner($bn_id);

        // @todo 파일 삭제처리 필요
        $this->service->delete($banner['bn_id']);

        return $response->withJson(['message' => '배너가 삭제되었습니다.']);
    }

    /**
     * 배너 전시순서 변경
     */
    public function update_order(Request $request, Response $response, string $bn_id): Response
    {
        $request_body = $request->getParsedBody();
        $this->service->updateOrder($request_body);

        return $this->redirectRoute($request, $response, 'admin.design.banner');
    }
}
