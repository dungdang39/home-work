<?php

namespace App\Base\Service;

use App\Base\Service\ThemeService;
use Core\Database\Db;

class ConfigService
{
    public const TABLE_NAME = 'config';

    protected array $cache = [];
    protected string $table;

    public function __construct()
    {
        $this->table = $_ENV['DB_PREFIX'] . self::TABLE_NAME;
    }

    /**
     * 현재 적용중인 테마 조회
     * @return string
     */
    public function getTheme(): string
    {
        return self::getConfig('design', 'theme') ?: ThemeService::DEFAULT_THEME;
    }

    /**
     * 활성화된 플러그인 목록 조회
     * @return array
     */
    public function getActivePlugins(): array
    {
        $active_plugins = $this->getConfig('config', 'active_plugins');
        return isset($active_plugins) ? explode(',', $active_plugins) : [];
    }

    public function getConfigs(?string $scope = null): array
    {
        if ($scope !== null && isset($this->cache[$scope])) {
            return $this->cache[$scope];
        }

        $configs = $this->fetchConfigs($scope);

        $result = [];
        foreach ($configs as $config) {
            $result[$config['name']] = $config['value'];
            $this->cache[$config['scope']][$config['name']] = $config['value'];
        }

        return $result;
    }

    /**
     * 환경설정 값 조회
     * @param string $scope 설정 범위
     * @param string $name 설정 이름
     * @param mixed $default 기본값
     * @return mixed 설정 값
     */
    public function getConfig(string $scope, string $name, $default = null)
    {
        if (isset($this->cache[$scope][$name])) {
            return $this->cache[$scope][$name];
        }

        $config = $this->fetch($scope, $name);
        if ($config) {
            return $config['value'];
        }

        return $default;
    }

    /**
     * 최고 관리자 여부 확인
     * @param string|null $mb_id 회원 아이디
     * @return bool
     */
    public function isSuperAdmin(?string $mb_id = null): bool
    {
        if (is_null($mb_id)) {
            return false;
        }
        return $this->getConfig('config', 'super_admin') === $mb_id;
    }

    /**
     * 환경설정 정보 추가 또는 수정
     * @param string $scope 설정 범위
     * @param array $data 설정 정보
     * @return void
     */
    public function upsertConfigs(string $scope, array $data)
    {
        $configs = $this->getConfigs($scope);

        foreach ($data as $name => $value) {
            if (is_null($value)) {
                continue;
            }
            if (array_key_exists($name, $configs)) {
                $this->update($scope, $name, $value);
            } else {
                $this->insert($scope, $name, $value);
            }
        }
    }

    /**
     * 환경설정 정보 삭제
     * @param string $scope 설정 범위
     * @param string $name 설정 이름
     * @return int 삭제된 행 수
     */
    public function deleteConfig(string $scope, string $name): int
    {
        return $this->delete($scope, $name);
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * 환경설정 목록 조회
     */
    protected function fetchConfigs(?string $scope = null): array
    {
        $wheres = [];
        $values = [];
        if (isset($scope)) {
            $wheres[] = 'scope = :scope';
            $values['scope'] = $scope;
        }
        $sql_where = $wheres ? 'WHERE ' . implode(' AND ', $wheres) : '';

        $query = "SELECT * FROM {$this->table} {$sql_where}";
        return Db::getInstance()->run($query, $values)->fetchAll();
    }

    /**
     * 환경설정 1건 조회
     * @param string $scope 설정 범위
     * @param string $name 설정 이름
     * @return array|false
     */
    protected function fetch(string $scope, string $name)
    {
        $query = "SELECT * FROM {$this->table} WHERE scope = :scope AND name = :name";
        $values = ['scope' => $scope, 'name' => $name];
        return Db::getInstance()->run($query, $values)->fetch();
    }

    /**
     * 환경설정 정보 추가
     * @param string $scope
     * @param string $name
     * @param mixed $value
     * @return int
     */
    public function insert(string $scope, string $name, $value): int
    {
        if (gettype($value) === 'boolean') {
            $value = $value ? 1 : 0;
        }

        return Db::getInstance()->insert(
            $this->table,
            ['scope' => $scope, 'name' => $name, 'value' => $value]
        );
    }

    /**
     * 환경설정 정보 수정
     * @param string $scope
     * @param string $name
     * @param mixed $value
     * @return int
     */
    public function update(string $scope, string $name, $value): int
    {
        if (gettype($value) === 'boolean') {
            $value = $value ? 1 : 0;
        }

        return Db::getInstance()->update(
            $this->table,
            ['value' => $value],
            ['scope' => $scope, 'name' => $name]
        );
    }

    /**
     * 환경설정 정보 삭제
     * @param string $scope
     * @param string $name
     * @return int
     */
    public function delete(string $scope, string $name): int
    {
        $query = "DELETE FROM {$this->table} WHERE scope = :scope AND name = :name";
        $values = ['scope' => $scope, 'name' => $name];
        return Db::getInstance()->run($query, $values)->rowCount();
    }
}
