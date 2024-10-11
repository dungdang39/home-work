<?php

namespace App\Social;

use Core\Database\Db;
use Slim\Http\ServerRequest as Request;

class SocialProfileService
{
    public const TABLE_NAME = 'member_social_profiles';

    public string $table;

    private Request $request;

    public function __construct(
        Request $request
    ) {
        $this->table = $_ENV['DB_PREFIX'] . self::TABLE_NAME;

        $this->request = $request;
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

    // ========================================
    // Database Queries
    // ========================================


    /**
     * 소셜 로그인 프로필 조회
     * @param string $provider
     * @param string $identifier
     * @return array|false
     */
    public function fetchByIdentifier(string $provider, string $identifier)
    {
        $values = ['provider' => $provider, 'identifier' => $identifier];
        $query = "SELECT * FROM {$this->table} WHERE identifier = ? AND provider = ?";

        return Db::getInstance()->run($query, $values)->fetch();
    }
}
