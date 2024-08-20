<?php

namespace App\Admin\Controller;

use App\Admin\Model\UpdateConfigRequest;
use App\Config\ConfigService;
use App\Member\MemberService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

class ConfigController
{
    private ConfigService $service;
    private MemberService $member_service;

    public function __construct(
        ConfigService $service,
        MemberService $member_service
    ) {
        $this->service = $service;
        $this->member_service = $member_service;
    }

    /**
     * 기본환경 설정 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        $config = $request->getAttribute('config');

        $admins = [];
        $fetch_admins = $this->member_service->fetchMemberByLevel(10);
        foreach ($fetch_admins as $admin) {
            $admins[] = $admin['mb_id'];
        }

        $response_data = [
            "config" => $config,
            "admins" => $admins
        ];
        return $view->render($response, '/admin/config_form.html', $response_data);
    }

    /**
     * 기본환경 설정 업데이트
     */
    public function update(Request $request, Response $response): Response
    {
        try {
            $request_body = $request->getParsedBody();
            $data = new UpdateConfigRequest($request_body);

            $this->service->update($data->toArray());
    
            // run_event('admin_config_form_update');
    
            $routeContext = RouteContext::fromRequest($request);
            $redirect_url = $routeContext->getRouteParser()->urlFor('config.index');
            return $response->withHeader('Location', $redirect_url)->withStatus(302);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
