<?php

namespace App\Base\Controller\Admin;

use App\Base\Model\Admin\BoardConfigRequest;
use App\Base\Model\Admin\CommunityConfigNotificationRequest;
use App\Base\Model\Admin\MemberConfigNotificationRequest;
use App\Base\Model\Admin\MemberConfigRequest;
use App\Base\Service\ConfigService;
use Core\BaseController;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class BoardConfigController extends BaseController
{
    private ConfigService $service;

    public function __construct(
        ConfigService $service,
    ) {
        $this->service = $service;
    }

    /**
     * 커뮤니티 > 커뮤니티 설정 > 기본환경설정 폼 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $configs = $this->service->getConfigs('community');

        $response_data = [
            "configs" => $configs,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/community/config/form.html', $response_data);
    }

    /**
     * 커뮤니티 > 커뮤니티 설정 > 기본환경설정 수정
     */
    public function update(Request $request, Response $response, BoardConfigRequest $data): Response
    {
        $this->service->upsertConfigs('community', $data->publics());

        return $this->redirectRoute($request, $response, 'admin.community.config.basic');
    }

    /**
     * 커뮤니티 > 커뮤니티 설정 > 알림/푸시 폼 페이지
     */
    public function indexNotification(Request $request, Response $response): Response
    {
        $configs = $this->service->getConfigs('community');

        $response_data = [
            "configs" => $configs,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/community/config/notification_form.html', $response_data);
    }

    /**
     * 커뮤니티 > 커뮤니티 설정 > 알림/푸시 수정
     */
    public function updateNotification(Request $request, Response $response, CommunityConfigNotificationRequest $data): Response
    {
        $this->service->upsertConfigs('community', $data->publics());

        return $this->redirectRoute($request, $response, 'admin.community.config.notification');
    }
}
