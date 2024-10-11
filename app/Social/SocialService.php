<?php

namespace App\Social;

use Core\Database\Db;
use PDOException;
use Slim\Exception\HttpNotFoundException;
use Slim\Http\ServerRequest as Request;

class SocialService
{
    public const TABLE_NAME = 'social_provider';
    public const KEY_TABLE_NAME = 'social_provider_key';

    public string $table;
    public string $key_table;

    private Request $request;

    public function __construct(
        Request $request
    ) {
        $this->table = $_ENV['DB_PREFIX'] . self::TABLE_NAME;
        $this->key_table = $_ENV['DB_PREFIX'] . self::KEY_TABLE_NAME;

        $this->request = $request;
    }

    /**
     * 소셜 로그인 설정 정보 가져오기
     * - Hybridauth\Hybridauth 클래스의 설정 형식으로 변환
     * 
     * @param Request $request
     * @param array $social
     * @return array
     */
    public function getSocialConfig(Request $request, array $social): array
    {
        $social['keys'] = $this->getSocialKeysToHybridauth($social['provider']);

        $class = $this->getClassName($social['provider'] . 'Config');
        return $class::getConfig($request, $social);
    }

    /**
     * 활성화된 소셜 로그인 제공자 목록 조회
     * @return array
     */
    public function getEnabledSocials(): array
    {
        $providers = $this->fetchSocials(['is_enabled' => 1]);
        if (!$providers) {
            return [];
        }
        return $providers;
    }

    /**
     * 등록 가능한 소셜 로그인 제공자 목록 조회
     * @param array $exclude  제외할 소셜 로그인 제공자 목록
     * @return array
     */
    public function getAvailableSocials(array $exclude = []): array
    {
        $providers = [];
        $fs = new \FilesystemIterator(__DIR__ . '/Provider/Config/');
        /** @var \SplFileInfo $file */
        foreach ($fs as $file) {
            if (!$file->isDir()) {
                $class_name = strtok($file->getFilename(), '.');
                $class = $this->getClassName($class_name);

                if (class_exists($class)) {
                    $provider = $class::getProvider();
                    if ($provider !== '' && in_array(strtolower($provider), $exclude)) {
                        continue;
                    }
                    $providers[$provider]['name'] = $class::getProviderName() ?? $provider;
                    $providers[$provider]['keys'] = $class::getKeys() ?? [];
                }
            }
        }
        return $providers;
    }

    /**
     * 소셜 로그인 제공자 목록 조회
     * @return array
     */
    public function getSocials(): array
    {
        $providers = $this->fetchSocials();
        if (!$providers) {
            return [];
        }

        foreach ($providers as &$provider) {
            $provider['keys'] = $this->fetchProviderKeys($provider['provider']) ?: [];
            foreach ($provider['keys'] as &$key) {
                $class = $this->getClassName($provider['provider'] . 'Config');
                $key['name'] = $class::getKeyName($key['key']);
            }
        }

        return $providers;
    }

    /**
     * 소셜 로그인 제공자 정보 조회
     * @return array
     * @throws HttpNotFoundException
     */
    public function getSocial(string $provider): array
    {
        $provider = $this->fetch($provider);
        if (!$provider) {
            throw new HttpNotFoundException($this->request, '소셜 로그인 설정이 존재하지 않습니다.');
        }

        return $provider;
    }

    /**
     * 소셜 로그인 제공자 키 정보를 Hybridauth 형식으로 변환
     * @param string $provider 소셜 로그인 제공자
     * @return array
     */
    public function getSocialKeysToHybridauth(string $provider): array
    {
        $keys = $this->fetchProviderKeys($provider);
        if (!$keys) {
            return [];
        }
        return array_column($keys, 'value', 'key');
    }

    /**
     * 소셜 로그인 제공자 및 키 정보 추가 
     * @param array $data 추가할 데이터
     * @return void
     * @throws PDOException
     */
    public function createSocial(array $data): void
    {
        Db::getInstance()->getPdo()->beginTransaction();

        $class = $this->getClassName($data['provider'] . 'Config');
        $provider = strtolower($data['provider']);

        $value = [
            'provider' => $provider,
            'provider_name' => $class::getProviderName(),
            'is_enabled' => 1,
        ];

        Db::getInstance()->insert($this->table, $value);

        foreach ($data['keys'] as $key => $value) {
            $key_value = [
                'provider' => $provider,
                'key' => $key,
                'value' => $value
            ];
            Db::getInstance()->insert($this->key_table, $key_value);
        }

        Db::getInstance()->getPdo()->commit();
    }

    /**
     * 소셜 로그인 제공자 및 키 정보 업데이트
     * @param array $data 업데이트할 데이터
     * @return bool
     * @throws PDOException
     */
    public function updateSocials(array $data): bool
    {
        Db::getInstance()->getPdo()->beginTransaction();

        foreach ($data['socials'] as $provider => $value) {
            $this->update($provider, ['is_enabled' => $value['is_enabled']]);

            if (isset($value['keys'])) {
                foreach ($value['keys'] as $key => $val) {
                    $this->updateKeys($provider, $key, ['value' => $val]);
                }
            }
        }

        Db::getInstance()->getPdo()->commit();

        return true;
    }

    /**
     * 소셜 로그인 삭제
     * @param string $provider 소셜 로그인 제공자
     * @return bool
     * @throws PDOException
     */
    public function deleteSocial(string $provider): bool
    {
        Db::getInstance()->getPdo()->beginTransaction();

        $this->deleteKeys($provider);
        $row_count = $this->delete($provider);

        Db::getInstance()->getPdo()->commit();

        return $row_count > 0;
    }

    /**
     * 소셜 로그인 제공자 클래스의 네임스페이스를 반환
     * 
     * @param string $class_name 소셜 로그인 제공자 이름
     * @return string 제공자 설정 클래스의 네임스페이스 경로
     */
    private function getClassName(string $class_name)
    {
        return sprintf('App\\Social\\Provider\\Config\\%s', ucfirst($class_name));
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * 소셜 로그인 제공자 목록정보 조회
     * @return array|false
     */
    public function fetchSocials(array $params = [])
    {
        $wheres = [];
        $values = [];

        if (isset($params['is_enabled'])) {
            $wheres[] = 'is_enabled = :is_enabled';
            $values['is_enabled'] = $params['is_enabled'];
        }

        $sql_where = $wheres ? 'WHERE ' . implode(' AND ', $wheres) : '';

        $query = "SELECT *
                    FROM {$this->table}
                    {$sql_where}";

        return Db::getInstance()->run($query, $values)->fetchAll();
    }

    /**
     * 소셜 로그인 제공자 키 목록정보 조회
     * @param string $provider 소셜로그인 제공자
     * @return array|false
     */
    public function fetchProviderKeys(string $provider)
    {
        $values = ['provider' => $provider];
        $query = "SELECT * FROM {$this->key_table} WHERE `provider` = :provider";

        return Db::getInstance()->run($query, $values)->fetchAll();
    }

    /**
     * 소셜 로그인 설정 조회
     * @param string $provider 소셜로그인 제공자
     * @return array|false
     */
    public function fetch(string $provider)
    {
        $values = ['provider' => $provider];
        $query = "SELECT * FROM {$this->table} WHERE provider = :provider";

        return Db::getInstance()->run($query, $values)->fetch();
    }

    /**
     * 소셜 로그인 설정이 존재하는지 확인
     * @param string $provider 소셜로그인 제공자
     * @return bool
     * @throws PDOException
     */
    public function exists(string $provider): bool
    {
        $values = ['provider' => $provider];
        $query = "SELECT * FROM {$this->table} WHERE provider = :provider";

        $stmt = Db::getInstance()->run($query, $values);
        return $stmt->rowCount() > 0;
    }

    /**
     * 소셜 로그인 설정 업데이트
     * @param string $provider  소셜로그인 제공자
     * @param array $data  업데이트할 데이터
     * @return int  업데이트된 행 수
     */
    public function update(string $provider, array $data): int
    {
        return Db::getInstance()->update($this->table, $data, ['provider' => $provider]);
    }

    /**
     * 소셜 로그인 키 설정 업데이트
     * @param string $provider  소셜로그인 제공자
     * @param array $data  업데이트할 데이터
     * @return int  업데이트된 행 수
     */
    public function updateKeys(string $provider, string $key, array $data): int
    {
        return Db::getInstance()->update($this->key_table, $data, ['provider' => $provider, 'key' => $key]);
    }

    /**
     * 소셜 로그인 설정 삭제
     * @param string $provider 소셜로그인 제공자
     * @return int  삭제된 행 수
     */
    public function delete(string $provider): int
    {
        return Db::getInstance()->delete($this->table, ['provider' => $provider]);
    }

    /**
     * 소셜 로그인 키 설정 삭제
     * @param string $provider_key 소셜로그인 제공자
     * @return int  삭제된 행 수
     */
    public function deleteKeys(string $provider): int
    {
        return Db::getInstance()->delete($this->key_table, ['provider' => $provider]);
    }
}
