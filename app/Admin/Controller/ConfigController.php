<?php

namespace App\Admin\Controller;

use App\Admin\Model\UpdateConfigRequest;
use App\Config\ConfigService;
use App\Member\MemberService;
use Core\BaseController;
use DI\Container;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class ConfigController extends BaseController
{
    private ConfigService $service;
    private MemberService $member_service;

    public function __construct(
        Container $container,
        ConfigService $service,
        MemberService $member_service
    ) {
        parent::__construct($container);

        $this->service = $service;
        $this->member_service = $member_service;
    }

    /**
     * 기본환경 설정 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $admins = $this->member_service->fetchMemberByLevel(10);

        $response_data = [
            "admins" => $admins,
            "current_ip" => getRealIp($request)
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/config_form.html', $response_data);
    }

    /**
     * 기본환경 설정 업데이트
     */
    public function update(Request $request, Response $response): Response
    {
        $data = UpdateConfigRequest::createFromRequestBody($request);

        $this->service->update($data->toArray());

        return $this->redirectRoute($request, $response, 'admin.setting.config');
    }
}
