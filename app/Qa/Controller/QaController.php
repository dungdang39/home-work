<?php

namespace App\Qa\Controller;

use App\Qa\Service\QaConfigService;
use App\Qa\Service\QaService;
use Core\Model\PageParameters;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;
use Exception;

class QaController
{
    private QaService $service;
    private QaConfigService $config_service;

    public function __construct(
        QaService $service,
        QaConfigService $config_service
    ) {
        $this->service = $service;
        $this->config_service = $config_service;
    }

    /**
     * Q&A 목록 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $query_params = $request->getQueryParams();
        $request_params = new QaSearchRequest($query_params);
        $page_params = new PageParameters($query_params);
        $params = array_merge($request_params->toArray(), $page_params->toArray());

        // Q&A 설정 조회
        $member_config = $this->service->getMemberConfig();

        // Q&A 목록 조회
        $members = $this->service->getMembers($params);

        $response_data = [
            "member_config" => $member_config,
            "members" => $members,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/member_list.html', $response_data);
    }

    /**
     * Q&A 등록 폼 페이지
     */
    public function create(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/member_form.html');
    }

    /**
     * Q&A 등록
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
            $redirect_url = $routeContext->getRouteParser()->urlFor('admin.member');
            return $response->withHeader('Location', $redirect_url)->withStatus(302);
        } catch (\Exception $e) {
            return api_response_json($response, [
                'result' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    /**
     * Q&A 상세 폼 페이지
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
     * Q&A 수정
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
                throw new Exception('자신보다 권한이 높거나 같은 Q&A은 수정할 수 없습니다.', 403);
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
            $redirect_url = $routeContext->getRouteParser()->urlFor('admin.member.view', ['mb_id' => $member['mb_id']]);
            return $response->withHeader('Location', $redirect_url)->withStatus(302);

        } catch (\Exception $e) {
            return api_response_json($response, [
                'result' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    /**
     * Q&A 삭제
     * - 실제 삭제하지 않고 탈퇴일자 및 Q&A메모를 업데이트한다.
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
                throw new Exception('자신보다 권한이 높거나 같은 Q&A은 삭제할 수 없습니다.', 403);
            }

            $this->service->leaveMember($member);

            return api_response_json($response, [
                'result' => 'success',
                'message' => 'Q&A이 삭제되었습니다.',
            ], 200);
        } catch (\Exception $e) {
            return api_response_json($response, [
                'result' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}
