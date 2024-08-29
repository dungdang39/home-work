<?php

namespace App\Member\Controller;

use App\Member\MemberConfigService;
use App\Member\Model\UpdateMemberConfigRequest;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

class MemberConfigController
{
    private MemberConfigService $service;

    public function __construct(
        MemberConfigService $service,
    ) {
        $this->service = $service;
    }

    /**
     * 회원 > 기본환경설정 폼 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $member_config = $this->service->getMemberConfig();

        $response_data = [
            "member_config" => $member_config,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/member_config_form.html', $response_data);
    }

    /**
     * 회원 > 기본환경설정 수정
     */
    public function update(Request $request, Response $response): Response
    {
        try {
            $request_body = $request->getParsedBody();
            $data = new UpdateMemberConfigRequest($request_body);

            $member_config = $this->service->getMemberConfig();

            if (empty($member_config)) {
                $this->service->insert($data->toArray());
            } else {
                $this->service->update($data->toArray());
            }

            // run_event('admin_member_config_form_update');
    
            $routeContext = RouteContext::fromRequest($request);
            $redirect_url = $routeContext->getRouteParser()->urlFor('admin.member-config');
            return $response->withHeader('Location', $redirect_url)->withStatus(302);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
