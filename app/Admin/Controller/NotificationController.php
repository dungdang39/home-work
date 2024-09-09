<?php

namespace App\Admin\Controller;

use App\Admin\Service\NotificationService;
use Core\BaseController;
use DI\Container;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class NotificationController extends BaseController
{
    private NotificationService $service;

    public function __construct(
        Container $container,
        NotificationService $service,
    ) {
        parent::__construct($container);

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
        return $view->render($response, '/admin/notification_form.html', $response_data);
    }

    /**
     * 알림 설정 정보 업데이트
     */
    public function update(Request $request, Response $response): Response
    {
        try {
            $request_body = $request->getParsedBody();

            foreach ($request_body['notification'] as $key => $data) {
                $this->service->update($key, $data);
            }
            foreach ($request_body['settings'] as $key => $value) {
                $this->service->updateSetting($key, $value);
            }
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        return $this->redirectRoute($request, $response, 'admin.setting.api.notification');
    }
}
