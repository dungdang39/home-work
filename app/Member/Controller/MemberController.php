<?php

namespace App\Member\Controller;

use App\Member\MemberConfigService;
use App\Member\MemberService;
use App\Member\Model\MemberSearchRequest;
use App\Member\Model\MemberUpdateRequest;
use App\Member\Model\MemberCreateRequest;
use Core\Model\PageParameters;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;
use Exception;

class MemberController
{
    private MemberService $service;
    private MemberConfigService $config_service;
    private MemberCreateRequest $create_request;
    private MemberUpdateRequest $update_request;

    public function __construct(
        MemberService $service,
        MemberConfigService $config_service,
        MemberCreateRequest $create_request,
        MemberUpdateRequest $update_request,
    ) {
        $this->service = $service;
        $this->config_service = $config_service;
        $this->create_request = $create_request;
        $this->update_request = $update_request;
    }

    /**
     * 회원 목록 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $query_params = $request->getQueryParams();
        $request_params = new MemberSearchRequest($query_params);
        $page_params = new PageParameters($query_params);
        $params = array_merge($request_params->toArray(), $page_params->toArray());

        // 회원 설정 조회
        $member_config = $this->config_service->getMemberConfig();

        // 회원 목록 조회
        $members = $this->service->getMembers($params);

        $response_data = [
            "member_config" => $member_config,
            "members" => $members,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/member_list.html', $response_data);
    }

    /**
     * 회원 등록 폼 페이지
     */
    public function create(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/member_form.html');
    }

    /**
     * 회원 등록
     * @todo 유효성검사 정상 동작 체크
     * @todo 아이디, 이메일, 닉네임 중복 검사 추가
     */
    public function insert(Request $request, Response $response): Response
    {
        try {
            $request_body = $request->getParsedBody();
            $data = $this->create_request->load($request_body);
    
            $this->service->createMember($data->toArray());
    
            $routeContext = RouteContext::fromRequest($request);
            $redirect_url = $routeContext->getRouteParser()->urlFor('admin.member.manage');
            return $response->withHeader('Location', $redirect_url)->withStatus(302);
        } catch (\Exception $e) {
            return api_response_json($response, [
                'result' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    /**
     * 회원 상세 폼 페이지
     */
    public function view(Request $request, Response $response, array $args): Response
    {
        $member = $this->service->getMember($args['mb_id']);

        $response_data = [
            "member" => $member,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/member_form.html', $response_data);
    }

    /**
     * 회원 수정
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $login_member = $request->getAttribute('member');
            $config = $request->getAttribute('config');

            $member = $this->service->getMember($args['mb_id']);
            $request_body = $request->getParsedBody();
            $data = $this->update_request->load($request_body, $member);
    
            $login_member_level = $login_member['mb_level'];
            $member_level = $member['mb_level'];

            if (!is_super_admin($config, $login_member['mb_id']) && $member_level >= $login_member_level) {
                throw new Exception('자신보다 권한이 높거나 같은 회원은 수정할 수 없습니다.', 403);
            }

            if (
                !is_super_admin($config, $login_member['mb_id'])
                && is_super_admin($config, $member['mb_id'])
            ) {
                throw new Exception('최고관리자의 비밀번호를 수정할수 없습니다.', 403);
            }
            if (
                $login_member['mb_id'] === $member['mb_id']
                && $member['mb_level'] != $data->mb_level
            ) {
                throw new Exception('로그인 중인 관리자 레벨은 수정할 수 없습니다.', 403);
            }
            if ($data->mb_leave_date || $data->mb_intercept_date) {
                if (
                    $login_member['mb_id'] === $member['mb_id']
                    || is_super_admin($config, $member['mb_id'])
                ) {
                        throw new Exception('해당 관리자의 탈퇴 일자 또는 접근 차단 일자를 수정할 수 없습니다.', 403);
                }
            }

            $this->service->updateMember($member['mb_id'], $data->toArray());
    
            $routeContext = RouteContext::fromRequest($request);
            $redirect_url = $routeContext->getRouteParser()->urlFor('admin.member.manage.view', ['mb_id' => $member['mb_id']]);
            return $response->withHeader('Location', $redirect_url)->withStatus(302);

        } catch (\Exception $e) {
            return api_response_json($response, [
                'result' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    /**
     * 회원 삭제
     * - 실제 삭제하지 않고 탈퇴일자 및 회원메모를 업데이트한다.
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $config = $request->getAttribute('config');
            $member = $this->service->getMember($args['mb_id']);
            $login_member = $request->getAttribute('member');

            if ($member === $login_member) {
                throw new Exception('로그인 중인 관리자는 삭제할 수 없습니다.', 403);
            }
            if (is_super_admin($config, $member['mb_id'])) {
                throw new Exception('최고 관리자는 삭제할 수 없습니다.', 403);
            }
            if ($member['mb_level'] >= $login_member['mb_level']) {
                throw new Exception('자신보다 권한이 높거나 같은 회원은 삭제할 수 없습니다.', 403);
            }

            $this->service->leaveMember($member);

            return api_response_json($response, [
                'result' => 'success',
                'message' => '회원이 삭제되었습니다.',
            ], 200);
        } catch (\Exception $e) {
            return api_response_json($response, [
                'result' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    /**
     * 회원정보 조회
     */
    public function getMemberInfo(Request $request, Response $response, array $args): Response
    {
        try {
            $member = $this->service->getMember($args['mb_id']);

            return api_response_json($response, [
                'result' => 'success',
                'member' => $member,
            ], 200);
        } catch (\Exception $e) {
            return api_response_json($response, [
                'result' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}