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
     * 등록 가능한 소셜 로그인 제공자 목록 조회
     * @param array $exclude  제외할 소셜 로그인 제공자 목록
     * @return array
     */
    public function getAvailableSocials(array $exclude = []): array
    {
        $providers = [];
        $fs = new \FilesystemIterator(__DIR__ . '/Provider/');
        /** @var \SplFileInfo $file */
        foreach ($fs as $file) {
            if (!$file->isDir()) {
                $provider = strtok($file->getFilename(), '.');

                if (in_array(strtolower($provider), $exclude)) {
                    continue;
                }

                $class = $this->getSocialClass($provider);
                if (class_exists($class)) {
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
                $class = $this->getSocialClass($provider['provider']);
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
     * 소셜 로그인 제공자 및 키 정보 추가 
     * @param array $data 추가할 데이터
     * @return void
     * @throws PDOException
     */
    public function createSocial(array $data): void
    {
        Db::getInstance()->getPdo()->beginTransaction();

        $class = $this->getSocialClass($data['provider']);
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
     * 소셜 로그인 제공자 클래스 조회
     * @param string $provider 소셜로그인 제공자
     * @return string
     */
    private function getSocialClass(string $provider)
    {
        return sprintf('App\\Social\\Provider\\%s', ucfirst($provider));
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * 소셜 로그인 제공자 목록정보 조회
     * @return array|false
     */
    public function fetchSocials()
    {
        return Db::getInstance()->run("SELECT * FROM {$this->table}")->fetchAll();
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
