<?php

namespace App\Base\Service;

use Core\Database\Db;

class MemberConfigService
{
    public const TABLE_NAME = 'member_config';
    private string $table;
    private array $member_config = [
        'use_point' => false,
        'point_term' => 0,
    ];

    public function __construct()
    {
        $this->table = $_ENV['DB_PREFIX'] . self::TABLE_NAME;
    }

    // ========================================
    // Database Queries
    // ========================================

    public function fetch()
    {
        $stmt = Db::getInstance()->run("SELECT * FROM {$this->table}");

        return $stmt->fetch();
    }

    public function insert(array $data): int
    {
        return Db::getInstance()->insert($this->table, $data);
    }

    public function update(array $data): int
    {
        return Db::getInstance()->update($this->table, $data);
    }

    // ========================================
    // Getters and Setters
    // ========================================

    /**
     * 회원 설정을 반환합니다.
     * @return array
     */
    public function getMemberConfig()
    {
        if (empty($this->member_config)) {
            $member_config = $this->fetch();
            if ($member_config) {
                $this->member_config = $member_config;
            }
        }

        return $this->member_config;
    }
}
