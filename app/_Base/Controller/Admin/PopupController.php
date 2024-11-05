<?php

namespace App\Base\Controller\Admin;

use App\Base\Model\Admin\PopupCreateRequest;
use App\Base\Model\Admin\PopupSearchRequest;
use App\Base\Model\Admin\PopupUpdateRequest;
use App\Base\Service\PopupService;
use Core\BaseController;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class PopupController extends BaseController
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
    public function index(Request $request, Response $response, PopupSearchRequest $search): Response
    {
        // 검색 조건 설정
        $params = $search->publics();

        // 총 데이터 수 조회 및 페이징 정보 설정
        $total_count = $this->service->fetchPopupsCount($params);
        $search->setTotalCount($total_count);
    
        // 팝업 목록 조회
        $popups = $this->service->getPopups($params);

        $response_data = [
            "popups" => $popups,
            "total_count" => $total_count,
            "search" => $search,
            "pagination" => $search->getPaginationInfo(),
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/design/popup/list.html', $response_data);
    }

    /**
     * 팝업 등록 폼 페이지
     */
    public function create(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/design/popup/form.html');
    }

    /**
     * 팝업 등록
     */
    public function insert(Request $request, Response $response, PopupCreateRequest $data): Response
    {
        $this->service->insert($data->publics());

        return $this->redirectRoute($request, $response, 'admin.design.popup');
    }

    /**
     * 팝업 상세 폼 페이지
     */
    public function view(Request $request, Response $response, string $pu_id): Response
    {
        $popup = $this->service->getPopup($pu_id);

        $response_data = [
            "popup" => $popup,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/design/popup/form.html', $response_data);
    }

    /**
     * 팝업 수정
     */
    public function update(Request $request, Response $response, PopupUpdateRequest $data, string $pu_id): Response
    {
        $popup = $this->service->getPopup($pu_id);

        $this->service->update($popup['pu_id'], $data->publics());

        return $this->redirectRoute($request, $response, 'admin.design.popup.view', ['pu_id' => $popup['pu_id']]);
    }

    /**
     * 팝업 삭제
     */
    public function delete(Response $response, string $pu_id): Response
    {
        $popup = $this->service->getPopup($pu_id);

        $this->service->delete($popup['pu_id']);

        return $response->withJson(['message' => '삭제되었습니다.']);
    }

    /**
     * 팝업 활성화/비활성화 전환
     */
    public function toggleEnabled(Request $request, Response $response, int $pu_id): Response
    {
        $popup = $this->service->getPopup($pu_id);
        $is_enabled = (bool)$popup['pu_is_enabled'];

        $this->service->update($popup['pu_id'], ['pu_is_enabled' => !$is_enabled]);

        return $response->withJson(['message' => '팝업이 ' . ($is_enabled ? '비' : '') . '활성화되었습니다.']);
    }
}
