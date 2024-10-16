<?php

namespace App\Member\Controller;

use App\Member\MemberConfigService;
use App\Member\Model\CreatePointRequest;
use App\Member\Model\DeletePointListRequest;
use App\Member\Model\PointSearchRequest;
use App\Member\PointService;
use Core\BaseController;
use Exception;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class PointController extends BaseController
{
    private array $member_config;
    private PointService $service;

    public function __construct(
        MemberConfigService $member_config_service,
        PointService $service,
    ) {
        $this->member_config = $member_config_service->getMemberConfig();
        $this->service = $service;
    }

    /**
     * 회원 > 포인트 관리 폼 페이지
     */
    public function index(Request $request, Response $response, PointSearchRequest $search_request): Response
    {
        // 검색 조건 설정
        $search_params = $search_request->publics();

        // 총 데이터 수 조회 및 페이징 정보 설정
        $total_count = $this->service->fetchPointsCount($search_params);
        $search_request->setTotalCount($total_count);

        $points = $this->service->getPoints($search_params);

        $response_data = [
            'point_term' => (int)$this->member_config['point_term'],
            'total_point' => $this->service->fetchTotalPoint(),
            'points' => $points,
            'total_count' => $total_count,
            'search' => $search_request,
            'pagination' => $search_request->getPaginationInfo(),
            'query_params' => $request->getQueryParams(),
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/member/point_form.html', $response_data);
    }

    /**
     * 회원 > 포인트 관리 > 포인트 추가
     */
    public function insert(Request $request, Response $response, CreatePointRequest $data): Response
    {
        if (empty($this->member_config['use_point'])) {
            throw new Exception('포인트 사용 설정이 되어 있지 않습니다.', 400);
        }

        $this->service->addPoint(
            $data->mb_id,
            $data->po_point,
            $data->po_content,
            '@passive',
            $data->mb_id,
            $data->mb_id . '-' . uniqid(''),
            $data->po_expire_term
        );

        return $this->redirectRoute($request, $response, 'admin.member.point', [], $request->getQueryParams());
    }

    /**
     * 회원 > 포인트 관리 > 포인트 만료 처리
     */
    public function update(Request $request, Response $response, int $po_id): Response
    {
        $point = $this->service->getPoint($po_id);

        if ($point['po_expired']) {
            throw new Exception('이미 만료처리된 포인트입니다.', 400);
        }
        if ($point['po_expire_date'] < date('Y-m-d')) {
            throw new Exception('만료일이 지난 포인트입니다.', 400);
        }

        $this->service->expirePoint($point);

        return $response->withJson(['message' => '포인트가 만료처리 되었습니다.']);
    }

    /**
     * 회원 > 포인트 관리 > 포인트 삭제
     */
    public function delete(Request $request, Response $response, int $po_id): Response
    {
        $point = $this->service->getPoint($po_id);

        $this->service->removePoint($point);

        return $response->withJson(['message' => '포인트가 삭제처리 되었습니다.']);
    }

    /**
     * 회원 > 포인트 관리 > 포인트 일괄 삭제
     */
    public function deleteList(Request $request, Response $response, DeletePointListRequest $data): Response
    {
        foreach ($data->po_ids as $po_id) {
            $point = $this->service->fetch($po_id);
            if (!$point) {
                continue;
            }

            $this->service->removePoint($point);
        }

        return $this->redirectRoute($request, $response, 'admin.member.point', [], $request->getQueryParams());
    }
}
