<?php

namespace App\Popup\Controller;

use App\Popup\Model\PopupCreateRequest;
use App\Popup\Model\PopupSearchRequest;
use App\Popup\Model\PopupUpdateRequest;
use App\Popup\PopupService;
use Core\Model\PageParameters;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

class PopupController
{
    private PopupService $service;

    public function __construct(
        PopupService $service,
    ) {
        $this->service = $service;
    }

    /**
     * 팝업 목록 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $query_params = $request->getQueryParams();
        $request_params = new PopupSearchRequest($query_params);
        $page_params = new PageParameters($query_params);
        $params = array_merge($request_params->toArray(), $page_params->toArray());

        $popups = $this->service->getPopups($params);

        $response_data = [
            "popups" => $popups,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/popup_list.html', $response_data);
    }

    /**
     * 팝업 등록 폼 페이지
     */
    public function create(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/popup_form.html');
    }

    /**
     * 팝업 등록
     */
    public function insert(Request $request, Response $response): Response
    {
        $request_body = $request->getParsedBody();
        $data = new PopupCreateRequest($request_body);

        $this->service->insert($data->toArray());

        $routeContext = RouteContext::fromRequest($request);
        $redirect_url = $routeContext->getRouteParser()->urlFor('admin.design.popup');
        return $response->withHeader('Location', $redirect_url)->withStatus(302);
    }

    /**
     * 팝업 상세 폼 페이지
     */
    public function view(Request $request, Response $response, array $args): Response
    {
        $popup = $this->service->getPopup($args['pu_id']);

        $response_data = [
            "popup" => $popup,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/popup_form.html', $response_data);
    }

    /**
     * 팝업 수정
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $popup = $this->service->getPopup($args['pu_id']);
        $request_body = $request->getParsedBody();
        $data = new PopupUpdateRequest($request_body);

        $this->service->update($popup['pu_id'], $data->toArray());

        $routeContext = RouteContext::fromRequest($request);
        $redirect_url = $routeContext->getRouteParser()->urlFor('admin.design.popup.view', ['pu_id' => $popup['pu_id']]);
        return $response->withHeader('Location', $redirect_url)->withStatus(302);
    }

    /**
     * 팝업 삭제
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $popup = $this->service->getPopup($args['pu_id']);

            $this->service->delete($popup['pu_id']);

            return api_response_json($response, [
                'result' => 'success',
                'message' => '팝업이 삭제되었습니다.',
            ], 200);
        } catch (\Exception $e) {
            return api_response_json($response, [
                'result' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}
