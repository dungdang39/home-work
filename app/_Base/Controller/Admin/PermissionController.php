<?php

namespace App\Base\Controller\Admin;

use App\Base\Model\Admin\CreatePermissionRequest;
use App\Base\Model\Admin\DeletePermissionListRequest;
use App\Base\Model\Admin\SearchPermissionRequest;
use App\Base\Model\Admin\UpdatePermissionRequest;
use App\Base\Service\PermissionService;
use App\Base\Service\MemberService;
use Core\BaseController;
use Core\Database\Db;
use Core\Exception\HttpConflictException;
use Core\Lib\FlashMessage;
use DI\Container;

use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

/**
 * 운영진 설정
 */
class PermissionController extends BaseController
{
    private FlashMessage $flash;
    private MemberService $member_service;
    private PermissionService $service;

    public function __construct(
        Container $container,
        MemberService $member_service,
        PermissionService $service
    ) {
        $this->flash = $container->get('flash');
        $this->member_service = $member_service;
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

        // 권한을 가진 회원 목록 조회
        $permission_members = $this->service->fetchPermissionMembers();

        $response_data = [
            "permission_members" => $permission_members,
            "permissions" => $permissions,
            "total_count" => $total_count,
            "search" => $search_request,
            "pagination" => $search_request->getPaginationInfo(),
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/config/permission_form.html', $response_data);
    }

    /**
     * 운영진 권한 추가
     */
    public function insert(Request $request, Response $response, CreatePermissionRequest $data): Response
    {
        $this->member_service->getMember($data->mb_id);
        $exists = $this->service->exists($data->mb_id, $data->admin_menu_id);
        if ($exists) {
            throw new HttpConflictException($request, '이미 등록된 권한입니다.');
        }

        $this->service->insert($data->publics());

        return $this->redirectRoute($request, $response, 'admin.config.permission');
    }

    /**
     * 운영진 권한 수정
     */
    public function update(Response $response, UpdatePermissionRequest $data, string $mb_id, string $admin_menu_id): Response
    {
        $member = $this->member_service->getMember($mb_id);
        $permission = $this->service->getPermission($member['mb_id'], $admin_menu_id);

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

    /**
     * 운영진 권한 일괄 삭제
     */
    public function deleteList(Request $request, Response $response, DeletePermissionListRequest $data): Response
    {
        Db::getInstance()->getPdo()->beginTransaction();

        foreach ($data->chk as $index) {
            $admin_menu_id = $data->admin_menu_id[$index];
            $mb_id = $data->mb_id[$index];
            $this->service->delete($mb_id, $admin_menu_id);
        }

        Db::getInstance()->getPdo()->commit();

        $this->flash->setMessage('삭제되었습니다.');
        
        return $this->redirectRoute($request, $response, 'admin.config.permission', [], $request->getQueryParams());
    }
}
