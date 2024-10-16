<?php

namespace App\Base\Controller\Admin;

use App\Base\Service\NotificationService;
use Core\BaseController;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class NotificationController extends BaseController
{
    private NotificationService $service;

    public function __construct(
        NotificationService $service,
    ) {
        $this->service = $service;
    }

    /**
     * 알림 설정 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $notifications = $this->service->getNotifications();

        $response_data = [
            "notifications" => $notifications
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/config/api/notification_form.html', $response_data);
    }

    /**
     * 알림 설정 정보 업데이트
     */
    public function update(Request $request, Response $response): Response
    {
        throw new \Exception('Not implemented');
        $request_body = $request->getParsedBody();

        foreach ($request_body['notification'] as $key => $data) {
            $this->service->update($key, $data);
        }
        foreach ($request_body['settings'] as $key => $value) {
            $this->service->updateSetting($key, $value);
        }

        return $this->redirectRoute($request, $response, 'admin.config.api.notification');
    }
}
