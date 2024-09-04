<?php

namespace App\Admin\Service;

use Core\Database\Db;
use PDOException;

class NotificationService
{
    public string $table;
    public string $setting_table;

    public function __construct()
    {
        $this->table = $_ENV['DB_PREFIX'] . 'notification';
        $this->setting_table = $_ENV['DB_PREFIX'] . 'notification_setting';
    }

    /**
     * 알림 설정 목록정보 조회
     * @return array
     */
    public function getNotifications(): array
    {
        $notifications = $this->fetchNotifications();

        if (empty($notifications)) {
            return [];
        }

        foreach ($notifications as &$noti) {
            $noti['settings'] = $this->fetchNotificationSettings($noti['id']);
        }

        return $notifications;
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
     * 알림의 설정값 목록 조회
     * @param int $notification_id  알림 ID
     * @return array
     */
    public function fetchNotificationSettings(int $notification_id): array
    {
        $values = ['notification_id' => $notification_id];
        $query = "SELECT * FROM {$this->setting_table} WHERE notification_id = :notification_id";

        try {
            $stmt = Db::getInstance()->run($query, $values);
            return $stmt->fetchAll() ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    public function update(string $type, array $data): int
    {
        try {
            return Db::getInstance()->update($this->table, $data, ['type' => $type]);
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function updateSetting(string $key, string $value): int
    {
        try {
            return Db::getInstance()->update(
                $this->setting_table,
                ['setting_value' => $value],
                ['setting_key' => $key]
            );
        } catch (PDOException $e) {
            return 0;
        }
    }
}
