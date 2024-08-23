<?php

namespace App\Member\Controller;

use App\Member\MemberConfigService;
use App\Member\MemberService;
use App\Member\Model\MemberSearchRequest;
use App\Member\Model\MemberUpdateRequest;
use App\Member\Model\MemberRequest;
use Core\Model\PageParameters;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

class MemberController
{
    private MemberService $service;
    private MemberConfigService $config_service;
    private MemberRequest $member_request;

    public function __construct(
        MemberService $service,
        MemberConfigService $config_service,
        MemberRequest $member_request,
    ) {
        $this->service = $service;
        $this->config_service = $config_service;
        $this->member_request = $member_request;
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
        $request_body = $request->getParsedBody();
        $data = $this->member_request->load($request_body);

        $this->service->insert($data->toArray());

        $routeContext = RouteContext::fromRequest($request);
        $redirect_url = $routeContext->getRouteParser()->urlFor('member.index');
        return $response->withHeader('Location', $redirect_url)->withStatus(302);
    }

    /**
     * 회원 상세 폼 페이지
     */
    public function view(Request $request, Response $response, array $args): Response
    {
        $member = $this->service->getMember($args['pu_id']);

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
        $member = $this->service->getMember($args['pu_id']);
        $request_body = $request->getParsedBody();
        $data = new MemberUpdateRequest($request_body);

        $this->service->update($member['pu_id'], $data->toArray());

        $routeContext = RouteContext::fromRequest($request);
        $redirect_url = $routeContext->getRouteParser()->urlFor('member.view', ['pu_id' => $member['pu_id']]);
        return $response->withHeader('Location', $redirect_url)->withStatus(302);
    }

    /**
     * 회원 삭제
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $member = $this->service->getMember($args['pu_id']);

            $this->service->delete($member['pu_id']);

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
}
