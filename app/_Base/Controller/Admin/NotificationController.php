<?php

namespace App\Base\Controller\Admin;

use App\Base\Model\Admin\UpdateNotificationRequest;
use App\Base\Service\NotificationService;
use Core\BaseController;
use Core\Database\Db;
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
        return $view->render($response, '@admin/config/api/notification_form.html', $response_data);
    }

    /**
     * 알림 설정 정보 업데이트
     */
    public function update(Request $request, Response $response, UpdateNotificationRequest $data): Response
    {
        Db::getInstance()->getPdo()->beginTransaction();

        foreach ($data->notifications as $noti => $value) {
            $settings = $value['settings'];
            unset($value['settings']);
            $this->service->update($noti, $value);

            foreach ($settings as $setting_key => $setting_value) {
                $this->service->updateSetting($setting_key, $setting_value);
            }
        }

        Db::getInstance()->getPdo()->commit();

        return $this->redirectRoute($request, $response, 'admin.config.api.notification');
    }
}
