<?php

namespace App\Admin\Service;

use Core\Database\Db;
use PDOException;

class SocialService
{
    public string $table;
    public string $config_table;

    public function __construct()
    {
        $this->table = $_ENV['DB_PREFIX'] . 'social_provider';
        $this->config_table = $_ENV['DB_PREFIX'] . 'social_provider_config';
    }

    /**
     * 소셜 로그인 제공자 목록 조회
     * @return array
     */
    public function getProviders(): array
    {
        $providers = [];

        $fetch_providers = $this->fetchProviders();
        foreach ($fetch_providers as $provider) {
            $provider['configs'] = $this->fetchProviderConfigs($provider['provider_key']);
            $providers[] = $provider;
        }

        return $providers;
    }

    /**
     * 소셜 로그인 제공자 추가
     * @param array $data  추가할 데이터
     * @return string  insert된 행의 ID
     */
    public function createProvider(array $data): string
    {
        $result = Db::getInstance()->insert($this->table, $data);
        if (!$result) {
            return "";
        }

        return $result;
    }

    /**
     * 소셜 로그인 제공자 삭제
     * @param string $provider_key  소셜로그인 제공자 키
     * @return bool
     * @throws PDOException
     */
    public function deleteProvider(string $provider_key): bool
    {
        $this->deleteConfig($provider_key);
        $row_count = $this->delete($provider_key);
        return $row_count > 0;
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * 소셜 로그인 제공자 목록정보 조회
     * @return array
     */
    public function fetchProviders(): array
    {
        try {
            $stmt = Db::getInstance()->run("SELECT * FROM {$this->table}");
            return $stmt->fetchAll() ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * 소셜 로그인 제공자 추가설정정보 조회
     * @param string $provider_key  소소로그인 제공자 키
     * @return array
     */
    public function fetchProviderConfigs(string $provider_key): array
    {
        $values = ['provider_key' => $provider_key];
        $query = "SELECT * FROM {$this->config_table} WHERE provider_key = :provider_key";

        try {
            $stmt = Db::getInstance()->run($query, $values);
            return $stmt->fetchAll() ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * 소셜 로그인이 존재하는지 확인
     * @param string $provider_key  소셜로그인 제공자 키
     * @return bool
     * @throws PDOException
     */
    public function isExistProvider(string $provider_key): bool
    {
        $values = ['provider_key' => $provider_key];
        $query = "SELECT * FROM {$this->table} WHERE provider_key = :provider_key";

        try {
            $stmt = Db::getInstance()->run($query, $values);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw $e;
        }
    }
    /**
     * 소셜 로그인 제공자 추가설정정보 업데이트
     * @param string $provider_key  소셜로그인 제공자 키
     * @param array $data  업데이트할 데이터
     * @return int  업데이트된 행 수
     */
    public function update(string $provider_key, array $data): int
    {
        try {
            return Db::getInstance()->update($this->table, $data, ['provider_key' => $provider_key]);
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * 소셜 로그인 제공자 정보 삭제
     * @param string $provider_key  소셜로그인 제공자 키
     * @return int  삭제된 행 수
     */
    public function delete(string $provider_key): int
    {
        return Db::getInstance()->delete($this->table, ['provider_key' => $provider_key]);
    }

    /**
     * 소셜 로그인 제공자 추가설정정보 삭제
     * @param string $provider_key  소셜로그인 제공자 키
     * @return int  삭제된 행 수
     */
    public function deleteConfig(string $provider_key): int
    {
        return Db::getInstance()->delete($this->config_table, ['provider_key' => $provider_key]);
    }
}
