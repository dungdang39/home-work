<?php

namespace App\Banner\Controller;

use App\Banner\BannerService;
use App\Banner\Model\BannerCreateRequest;
use App\Banner\Model\BannerSearchRequest;
use App\Banner\Model\BannerUpdateRequest;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

class BannerController
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
        $request_body = $request->getParsedBody();
        $request_file = $request->getUploadedFiles();
        $data = new BannerCreateRequest($request_body, $request_file);

        $this->service->makeBannerDir($request);
        $this->service->uploadImage($request, $data);
        $this->service->insert($data->toArray());

        $routeContext = RouteContext::fromRequest($request);
        $redirect_url = $routeContext->getRouteParser()->urlFor('banner.index');
        return $response->withHeader('Location', $redirect_url)->withStatus(302);
    }

    /**
     * 배너 상세 폼 페이지
     */
    public function view(Request $request, Response $response, array $args): Response
    {
        $banner = $this->service->getBanner($args['bn_id']);
        if (empty($banner)) {
            throw new \Exception('배너 정보를 찾을 수 없습니다.');
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
    public function update(Request $request, Response $response, array $args): Response
    {
        $banner = $this->service->getBanner($args['bn_id']);
        if (empty($banner)) {
            throw new \Exception('배너 정보를 찾을 수 없습니다.');
        }

        $request_body = $request->getParsedBody();
        $request_file = $request->getUploadedFiles();
        $data = new BannerUpdateRequest($request_body, $request_file);

        // @todo 기존 파일 삭제처리 필요
        $this->service->uploadImage($request, $data);
        $this->service->update($args['bn_id'], $data->toArray());

        $routeContext = RouteContext::fromRequest($request);
        $redirect_url = $routeContext->getRouteParser()->urlFor('banner.view', ['bn_id' => $args['bn_id']]);
        return $response->withHeader('Location', $redirect_url)->withStatus(302);
    }

    /**
     * 배너 삭제
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        $banner = $this->service->getBanner($args['bn_id']);
        if (empty($banner)) {
            return api_response_json($response, [
                'result' => 'error',
                'message' => '배너 정보를 찾을 수 없습니다.',
            ], 200);
        }

        // @todo 파일 삭제처리 필요
        $this->service->delete($args['bn_id']);

        return api_response_json($response, [
            'result' => 'success',
            'message' => '배너가 삭제되었습니다.',
        ], 200);
    }

    /**
     * 배너 전시순서 변경
     */
    public function update_order(Request $request, Response $response, array $args): Response
    {
        $request_body = $request->getParsedBody();
        $this->service->updateOrder($request_body);

        $routeContext = RouteContext::fromRequest($request);
        $redirect_url = $routeContext->getRouteParser()->urlFor('banner.index');
        return $response->withHeader('Location', $redirect_url)->withStatus(302);
    }
}
