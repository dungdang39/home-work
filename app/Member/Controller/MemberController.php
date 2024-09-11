<?php

namespace App\Member\Controller;

use App\Member\MemberConfigService;
use App\Member\MemberService;
use App\Member\Model\CreateMemberRequest;
use App\Member\Model\MemberSearchRequest;
use App\Member\Model\MemberRequest;
use Core\BaseController;
use Core\Model\PageParameters;
use DI\Container;
use Exception;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class MemberController extends BaseController
{
    private MemberService $service;
    private MemberConfigService $config_service;
    private CreateMemberRequest $create_request;
    private MemberRequest $update_request;

    public function __construct(
        Container $container,
        MemberService $service,
        MemberConfigService $config_service,
        CreateMemberRequest $create_request,
        MemberRequest $update_request,
    ) {
        parent::__construct($container);

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
        // 회원 설정 조회
        $member_config = $this->config_service->getMemberConfig();

        // 검색 조건 설정
        $search_params = MemberSearchRequest::createFromQueryParams($request)->toArray();

        // 페이지 설정
        $total_count = $this->service->fetchMembersTotalCount($search_params);
        $page_params = PageParameters::createFromQueryParams($request)->toArray();
        $page_params['total_count'] = $total_count;
        $page_params['total_page'] = ceil($total_count / $page_params['limit']);
        $params = array_merge($search_params, $page_params);

        // 회원 목록 조회
        $members = $this->service->getMembers($params);

        $response_data = [
            "member_config" => $member_config,
            "members" => $members,
            "total_count" => $total_count,
            "search" => $search_params,
            "pagination" => $page_params,
            "query_params" => $request->getQueryParams(),
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
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        return $this->redirectRoute($request, $response, 'admin.member.manage');
    }

    /**
     * 회원 상세 폼 페이지
     */
    public function view(Request $request, Response $response, string $mb_id): Response
    {
        try {
            $member = $this->service->getMember($mb_id);
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        $response_data = [
            "member" => $member,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/member_form.html', $response_data);
    }

    /**
     * 회원 수정
     */
    public function update(Request $request, Response $response, string $mb_id): Response
    {
        try {
            $login_member = $request->getAttribute('login_member');
            $config = $request->getAttribute('config');

            $member = $this->service->getMember($mb_id);
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

            $this->service->updateMember($member['mb_id'], get_object_vars($data));
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        return $this->redirectRoute($request, $response, 'admin.member.manage.view', ['mb_id' => $member['mb_id']]);
    }

    /**
     * 회원 삭제
     * - 실제 삭제하지 않고 탈퇴일자 및 회원메모를 업데이트한다.
     */
    public function delete(Request $request, Response $response, string $mb_id): Response
    {
        try {
            $config = $request->getAttribute('config');
            $member = $this->service->getMember($mb_id);
            $login_member = $request->getAttribute('login_member');

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
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        return $this->redirectRoute($request, $response, 'admin.member.manage');
    }

    public function deleteList(Request $request, Response $response): Response
    {
        return $this->redirectRoute($request, $response, 'admin.member.manage');
    }

    /**
     * 회원정보 조회
     */
    public function getMemberInfo(Request $request, Response $response, string $mb_id): Response
    {
        $member = $this->service->getMember($mb_id);

        return $response->withJson([
            'message' => "회원정보 조회가 완료되었습니다.",
            'member' => $member
        ], 200);
    }
}
