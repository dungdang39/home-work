<?php

namespace App\Base\Controller\Admin;

use App\Base\Model\Admin\MemberConfigNotificationRequest;
use App\Base\Model\Admin\MemberConfigRequest;
use App\Base\Service\ConfigService;
use Core\BaseController;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class MemberConfigController extends BaseController
{
    private ConfigService $service;

    public function __construct(
        ConfigService $service,
    ) {
        $this->service = $service;
    }

    /**
     * 회원 > 회원설정 > 기본환경설정 폼 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $configs = $this->service->getConfigs('member');

        $response_data = [
            "configs" => $configs,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/member/config/form.html', $response_data);
    }

    /**
     * 회원 > 회원설정 > 기본환경설정 수정
     */
    public function update(Request $request, Response $response, MemberConfigRequest $data): Response
    {
        $this->service->upsertConfigs('member', $data->publics());

        return $this->redirectRoute($request, $response, 'admin.member.config.basic');
    }

    /**
     * 회원 > 회원설정 > 알림/푸시 폼 페이지
     */
    public function indexNotification(Request $request, Response $response): Response
    {
        $configs = $this->service->getConfigs('member');

        $response_data = [
            "configs" => $configs,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/member/config/notification_form.html', $response_data);
    }

    /**
     * 회원 > 회원설정 > 알림/푸시 수정
     */
    public function updateNotification(Request $request, Response $response, MemberConfigNotificationRequest $data): Response
    {
        $this->service->upsertConfigs('member', $data->publics());

        return $this->redirectRoute($request, $response, 'admin.member.config.notification');
    }
}
