<?php

namespace App\Social;

use Core\Database\Db;
use Slim\Http\ServerRequest as Request;

class SocialProfileService
{
    public const TABLE_NAME = 'member_social_profiles';

    public string $table;

    private Request $request;
    private SocialService $social_service;

    public function __construct(
        Request $request,
        SocialService $social_service
    ) {
        $this->table = $_ENV['DB_PREFIX'] . self::TABLE_NAME;

        $this->request = $request;
        $this->social_service = $social_service;
    }

    /**
     * 회원의 소셜 로그인 정보 조회
     * @param int $mb_id 회원 아이디
     * @return array
     */
    public function getMemberSocialProfiles(string $mb_id): array
    {
        $profiles = $this->fetchProfiles($mb_id);
        if (!$profiles) {
            return [];
        }

        return $profiles;
    }

    /**
     * 소셜 로그인 프로필 조회
     * @param string $provider
     * @param string $identifier
     * @return array
     */
    public function getProfile(string $provider, string $identifier): array
    {
        $social_profile = $this->fetchByIdentifier($provider, $identifier);
        if (!$social_profile || empty($social_profile['mb_id'])) {
            return [];
        }
        return $social_profile;
    }

    /**
     * 소셜로그인 연결 끊기
     * @param string $provider
     * @param string $mb_id
     * @return void
     */
    public function unlink(string $provider, string $mb_id)
    {
        // $count = $this->countProfileByMemeberId($mb_id);
        // if ($count < 1) {
        //     return;
        // }

        // // 템플릿 버전과 다른점
        // if ($count === 1) {
        //     throw new \RuntimeException('연결된 계정이 하나뿐인 경우 해제할 수 없습니다.', 400);
        // }

        $this->delete($provider, $mb_id);
    }

    // ========================================
    // Database Queries
    // ========================================


    public function fetchProfiles(string $mb_id): array
    {
        $wheres = [];
        $values = [];

        $wheres[] = 'mb_id = :mb_id';
        $values['mb_id'] = $mb_id;

        $sql_where = $wheres ? 'WHERE ' . implode(' AND ', $wheres) : '';

        $query = "SELECT profile.*, social.provider_name
                    FROM {$this->table} profile
                    LEFT JOIN {$this->social_service->table} social ON profile.provider = social.provider
                    {$sql_where}";

        return Db::getInstance()->run($query, $values)->fetchAll();
    }

    /**
     * 소셜 로그인 프로필 조회
     * @param string $provider
     * @param string $identifier
     * @return array|false
     */
    public function fetchByIdentifier(string $provider, string $identifier)
    {
        $values = ['provider' => $provider, 'identifier' => $identifier];
        $query = "SELECT * FROM {$this->table} WHERE provider = :provider AND identifier = :identifier";

        return Db::getInstance()->run($query, $values)->fetch();
    }

    /**
     * 소셜 로그인 프로필 삭제
     * @param string $provider 소셜로그인 제공자
     * @param string $mb_id 회원아이디
     * @return bool
     */
    public function delete(string $provider, string $mb_id): bool
    {
        $row_count = Db::getInstance()->delete($this->table, ['provider' => $provider, 'mb_id' => $mb_id]);

        return $row_count > 0;
    }
}
