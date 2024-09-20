<?php

namespace App\Admin\Controller;

use App\Admin\Model\CreatePermissionRequest;
use App\Admin\Model\SearchPermissionRequest;
use App\Admin\Model\UpdatePermissionRequest;
use App\Admin\Service\PermissionService;
use Core\BaseController;
use DI\Container;
use Exception;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

/**
 * 운영진 설정
 * @todo 목록 > 검색 개선 및 일괄삭제
 * @todo 운영진 추가 > 검색 (레이어팝업)
 */
class PermissionController extends BaseController
{
    private PermissionService $service;

    public function __construct(
        Container $container,
        PermissionService $service
    ) {
        parent::__construct($container);

        $this->service = $service;
    }

    /**
     * 운영진 권한 목록
     */
    public function index(Request $request, Response $response, SearchPermissionRequest $search_request): Response
    {
        // 검색 조건 설정
        $search_params = $search_request->publics();

        // 총 데이터 수 조회 및 페이징 정보 설정
        $total_count = $this->service->fetchPermissionsTotalCount($search_params);
        $search_request->setTotalCount($total_count);

        // 권한 목록 조회
        $permissions = $this->service->getPermissions($search_params);

        $response_data = [
            "permissions" => $permissions,
            "total_count" => $total_count,
            "search" => $search_request,
            "pagination" => $search_request->getPaginationInfo(),
            "query_params" => $request->getQueryParams(),
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/permission_form.html', $response_data);
    }

    /**
     * 운영진 권한 추가
     */
    public function insert(Request $request, Response $response, CreatePermissionRequest $data): Response
    {
        try {
            $exists = $this->service->exists($data->mb_id, $data->admin_menu_id);
            if ($exists) {
                throw new Exception('이미 등록된 권한입니다.', 409);
            }

            $this->service->insert($data->publics());
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        return $this->redirectRoute($request, $response, 'admin.setting.permission');
    }

    /**
     * 운영진 권한 수정
     */
    public function update(Response $response, UpdatePermissionRequest $data, string $mb_id, string $admin_menu_id): Response
    {
        $permission = $this->service->getPermission($mb_id, $admin_menu_id);

        $this->service->update(
            $permission['mb_id'],
            $permission['admin_menu_id'],
            $data->publics()
        );

        return $response->withJson(['message' => '수정되었습니다.']);
    }

    /**
     * 운영진 권한 삭제
     */
    public function delete(Response $response, string $mb_id, string $admin_menu_id): Response
    {
        $permission = $this->service->getPermission($mb_id, $admin_menu_id);

        $this->service->delete($permission['mb_id'], $permission['admin_menu_id']);

        return $response->withJson(['message' => '삭제되었습니다.']);
    }
}
