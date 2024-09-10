<?php

namespace App\Popup\Controller;

use App\Popup\Model\PopupCreateRequest;
use App\Popup\Model\PopupSearchRequest;
use App\Popup\Model\PopupUpdateRequest;
use App\Popup\PopupService;
use Core\BaseController;
use Core\Model\PageParameters;
use DI\Container;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class PopupController extends BaseController
{
    private PopupService $service;

    public function __construct(
        Container $container,
        PopupService $service,
    ) {
        parent::__construct($container);

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
        try {
            $request_body = $request->getParsedBody();
            $data = new PopupCreateRequest($request_body);

            $this->service->insert($data->toArray());
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        return $this->redirectRoute($request, $response, 'admin.design.popup');
    }

    /**
     * 팝업 상세 폼 페이지
     */
    public function view(Request $request, Response $response, string $pu_id): Response
    {
        try {
            $popup = $this->service->getPopup($pu_id);
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        $response_data = [
            "popup" => $popup,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/popup_form.html', $response_data);
    }

    /**
     * 팝업 수정
     */
    public function update(Request $request, Response $response, string $pu_id): Response
    {
        try {
            $popup = $this->service->getPopup($pu_id);
            $request_body = $request->getParsedBody();
            $data = new PopupUpdateRequest($request_body);

            $this->service->update($popup['pu_id'], $data->toArray());
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        return $this->redirectRoute($request, $response, 'admin.design.popup.view', ['pu_id' => $popup['pu_id']]);
    }

    /**
     * 팝업 삭제
     */
    public function delete(Request $request, Response $response, string $pu_id): Response
    {
        $popup = $this->service->getPopup($pu_id);

        $this->service->delete($popup['pu_id']);

        return $this->responseJson($response, '팝업이 삭제되었습니다.');
    }
}
