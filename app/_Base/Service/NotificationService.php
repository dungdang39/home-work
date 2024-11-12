<?php

namespace App\Base\Service;

use Core\Database\Db;
use Slim\Exception\HttpNotFoundException;
use Slim\Http\ServerRequest as Request;

class NotificationService
{
    public const TABLE_NAME = 'notification';
    public const SETTING_TABLE_NAME = 'notification_setting';

    public string $table;
    public string $setting_table;

    private Request $request;

    public function __construct(
        Request $request
    ){
        $this->request = $request;

        $this->table = $_ENV['DB_PREFIX'] . self::TABLE_NAME;
        $this->setting_table = $_ENV['DB_PREFIX'] . self::SETTING_TABLE_NAME;
    }

    /**
     * 알림 설정 목록 조회
     * @return array
     */
    public function getNotifications(): array
    {
        $notifications = $this->fetchNotifications();
        if (empty($notifications)) {
            return [];
        }

        foreach ($notifications as &$noti) {
            $noti['settings'] = $this->getSettings($noti['id']);
        }

        return $notifications;
    }

    /**
     * 알림 정보 조회
     * @param string $type  알림 타입
     * @return array
     * @throws HttpNotFoundException
     */
    public function getNotificationByType(string $type): array
    {
        $notification = $this->fetchNotificationByType($type);
        if (empty($notification)) {
            throw new HttpNotFoundException($this->request, '알림 정보를 찾을 수 없습니다.');
        }
        $notification['settings'] = $this->getSettings($notification['id']);

        return $notification;
    }

    /**
     * 알림 설정값 조회
     * @param int $notification_id  알림 ID
     * @return array
     */
    public function getSettings(int $notification_id): array
    {
        $settings = $this->fetchSettings($notification_id);
        if (empty($settings)) {
            return [];
        }

        foreach ($settings as &$setting) {
            $setting['setting_options'] = json_decode($setting['setting_options'], true);
        }

        return $settings;
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * 알림 설정 목록 조회
     * @return array|false
     */
    public function fetchNotifications()
    {
        return Db::getInstance()->run("SELECT * FROM {$this->table}")->fetchAll();
    }

    /**
     * 알림 타입으로 정보 조회
     * @param string $type  알림 타입
     * @return array|false
     */
    public function fetchNotificationByType(string $type)
    {
        $values = ['type' => $type];
        $query = "SELECT * FROM {$this->table} WHERE type = :type";

        return Db::getInstance()->run($query, $values)->fetch();
    }

    /**
     * 알림의 설정값 목록 조회
     * @param int $notification_id  알림 ID
     * @return array
     */
    public function fetchSettings(int $notification_id): array
    {
        $values = ['notification_id' => $notification_id];
        $query = "SELECT * FROM {$this->setting_table} WHERE notification_id = :notification_id";

        return Db::getInstance()->run($query, $values)->fetchAll();
    }

    /**
     * 알림 설정 정보 업데이트
     * @param string $type  알림 타입
     * @param array $data   업데이트할 데이터
     * @return int
     */
    public function update(string $type, array $data): int
    {
        return Db::getInstance()->update($this->table, $data, ['type' => $type]);
    }

    /**
     * 설정값 업데이트
     * @param string $key    설정 키
     * @param string $value  설정 값
     * @return int
     */
    public function updateSetting(string $key, string $value): int
    {
        return Db::getInstance()->update(
            $this->setting_table,
            ['setting_value' => $value],
            ['setting_key' => $key]
        );
    }
}
