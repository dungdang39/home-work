<?php

namespace App\Base\Controller\Admin;

use App\Base\Service\MemberConfigService;
use App\Base\Model\Admin\MemberConfigNotificationRequest;
use App\Base\Model\Admin\MemberConfigRequest;
use Core\BaseController;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class MemberConfigController extends BaseController
{
    private MemberConfigService $service;

    public function __construct(
        MemberConfigService $service,
    ) {
        $this->service = $service;
    }

    /**
     * 회원 > 회원설정 > 기본환경설정 폼 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $member_config = $this->service->getMemberConfig();

        $response_data = [
            "member_config" => $member_config,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/member/config/form.html', $response_data);
    }

    /**
     * 회원 > 회원설정 > 기본환경설정 수정
     */
    public function update(Request $request, Response $response, MemberConfigRequest $data): Response
    {
        $member_config = $this->service->getMemberConfig();
        $publics = $data->publics();

        if (empty($member_config)) {
            $this->service->insert($publics);
        } else {
            $this->service->update($publics);
        }

        return $this->redirectRoute($request, $response, 'admin.member.config.basic');
    }

    /**
     * 회원 > 회원설정 > 알림/푸시 폼 페이지
     */
    public function indexNotification(Request $request, Response $response): Response
    {
        $member_config = $this->service->getMemberConfig();

        $response_data = [
            "member_config" => $member_config,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/member/config/notification_form.html', $response_data);
    }

    /**
     * 회원 > 회원설정 > 알림/푸시 수정
     */
    public function updateNotification(Request $request, Response $response, MemberConfigNotificationRequest $data): Response
    {
        $member_config = $this->service->getMemberConfig();
        $publics = $data->publics();

        if (empty($member_config)) {
            $this->service->insert($publics);
        } else {
            $this->service->update($publics);
        }

        return $this->redirectRoute($request, $response, 'admin.member.config.notification');
    }
}