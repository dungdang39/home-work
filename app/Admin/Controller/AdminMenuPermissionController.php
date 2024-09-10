<?php

namespace App\Admin\Controller;

use App\Admin\Model\AdminMenuPermissionRequest;
use App\Admin\Model\AdminMenuPermissionSearchRequest;
use App\Admin\Model\CreateAdminMenuPermissionRequest;
use App\Admin\Service\AdminMenuPermissionService;
use Core\BaseController;
use Core\Model\PageParameters;
use DI\Container;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

/**
 * 운영진 설정
 * @todo 목록 > 검색 개선 및 일괄삭제
 * @todo 운영진 추가 > 검색 (레이어팝업)
 */
class AdminMenuPermissionController extends BaseController
{
    private AdminMenuPermissionService $service;

    public function __construct(
        Container $container,
        AdminMenuPermissionService $service
    ) {
        parent::__construct($container);

        $this->service = $service;
    }

    /**
     * 운영진 권한 목록
     */
    public function index(Request $request, Response $response): Response
    {
        $request_params = AdminMenuPermissionSearchRequest::createFromQueryParams($request)->toArray();
        $page_params = PageParameters::createFromQueryParams($request)->toArray();
        $search_params = array_merge($request_params, $page_params);

        $permissions = $this->service->getPermissions($search_params);

        $response_data = [
            "permissions" => $permissions,
            "params" => $request_params,
            "pagination" => $page_params,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/permission_form.html', $response_data);
    }

    /**
     * 운영진 권한 추가
     */
    public function insert(Request $request, Response $response): Response
    {
        try {
            $data = CreateAdminMenuPermissionRequest::createFromRequestBody($request);
            $permission = $this->service->fetch($data->mb_id, $data->admin_menu_id);
            if ($permission) {
                throw new Exception('이미 등록된 운영진&권한입니다.');
            }

            $this->service->insert($data->toArray());
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        return $this->redirectRoute($request, $response, 'admin.setting.permission');
    }

    /**
     * 운영진 권한 수정
     */
    public function update(Request $request, Response $response, string $mb_id, string $admin_menu_id): Response
    {
        try {
            $permission = $this->service->getPermission($mb_id, $admin_menu_id);

            $data = AdminMenuPermissionRequest::createFromRequestBody($request);

            $this->service->update(
                $permission['mb_id'],
                $permission['admin_menu_id'],
                $data->toArray()
            );
        } catch (Exception $e) {
            return $this->responseJson($response, $e->getMessage(), $e->getCode());
        }

        return $this->responseJson($response, '수정되었습니다.');
    }

    /**
     * 운영진 권한 삭제
     */
    public function delete(Request $request, Response $response, string $mb_id, string $admin_menu_id): Response
    {
        try {
            $permission = $this->service->getPermission($mb_id, $admin_menu_id);

            $this->service->delete($permission['mb_id'], $permission['admin_menu_id']);
        } catch (Exception $e) {
            return $this->responseJson($response, $e->getMessage(), $e->getCode());
        }

        return $this->responseJson($response, '삭제되었습니다.');
    }
}
