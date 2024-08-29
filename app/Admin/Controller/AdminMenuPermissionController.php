<?php

namespace App\Admin\Controller;

use App\Admin\Model\AdminMenuPermissionRequest;
use App\Admin\Model\AdminMenuPermissionSearchRequest;
use App\Admin\Service\AdminMenuPermissionService;
use Core\Model\PageParameters;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

/**
 * 운영진 설정
 * @todo 목록 > 검색 및 일괄삭제
 * @todo 운영진 추가 > 검색 (레이어팝업)
 */
class AdminMenuPermissionController
{
    private AdminMenuPermissionService $service;

    public function __construct(
        AdminMenuPermissionService $service
    ) {
        $this->service = $service;
    }

    /**
     * 운영진 권한 목록
     */
    public function index(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        $query_params = $request->getQueryParams();
        $request_params = AdminMenuPermissionSearchRequest::load($query_params)->toArray();
        $page_params = PageParameters::load($query_params)->toArray();
        $search_params = array_merge($request_params, $page_params);

        $permissions = $this->service->getPermissions($search_params);

        $response_data = [
            "permissions" => $permissions,
            "params" => $request_params,
            "pagination" => $page_params,

        ];
        return $view->render($response, '/admin/administrator_form.html', $response_data);
    }

    /**
     * 운영진 권한 추가
     */
    public function insert(Request $request, Response $response): Response
    {
        $request_body = $request->getParsedBody();
        $data = new AdminMenuPermissionRequest($request_body);

        if ($this->service->fetch($data->mb_id, $data->admin_menu_id)) {
            throw new Exception('이미 등록된 운영진입니다.');
        }

        $this->service->insert($data->toArray());

        $routeContext = RouteContext::fromRequest($request);
        $redirect_url = $routeContext->getRouteParser()->urlFor('admin.administrator');
        return $response->withHeader('Location', $redirect_url)->withStatus(302);
    }

    /**
    * 운영진 권한 수정
    */
    public function update(Request $request, Response $response, array $args): Response
    {
        $request_body = $request->getParsedBody();
        $request_body['mb_id'] = $args['mb_id'];
        $request_body['admin_menu_id'] = $args['admin_menu_id'];

        try {
            $data = AdminMenuPermissionRequest::load($request_body)->toArray();
            
            $permission = $this->service->getPermission($args['mb_id'], $args['admin_menu_id']);
    
            $this->service->update($permission['mb_id'], $permission['admin_menu_id'], $data);
        } catch (Exception $e) {
            return api_response_json(
                $response,
                ['result' => 'error', 'message' => $e->getMessage()],
                $e->getCode()
            );
        }

        return api_response_json(
            $response,
            ['result' => 'success', 'message' => '수정되었습니다.'],
            200
        );
    }

    /**
     * 운영진 권한 삭제
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $permission = $this->service->getPermission($args['mb_id'], $args['admin_menu_id']);
    
            $this->service->delete($permission['mb_id'], $permission['admin_menu_id']);
        } catch (Exception $e) {
            return api_response_json(
                $response,
                ['result' => 'error', 'message' => $e->getMessage()],
                $e->getCode()
            );
        }

        return api_response_json(
            $response,
            ['result' => 'success', 'message' => '삭제되었습니다.'],
            200
        );
    }
}
