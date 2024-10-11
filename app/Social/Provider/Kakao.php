<?php

namespace App\Social\Provider;

use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User;

/**
 * Kakao OAuth2 provider adapter.
 * @see https://developers.kakao.com/docs/latest/ko/kakaologin/rest-api
 * @see https://devtalk.kakao.com/t/how-to-set-scopes-to-required-consent/115162
 */
class Kakao extends OAuth2
{
    /**
     * 개인정보 제공 동의 항목
     * - account_email은 비즈앱 신청을 해야 사용 가능
     * @see https://devtalk.kakao.com/t/how-to-set-scopes-to-required-consent/115162
     * @var string
     */
    protected $scope = 'profile_nickname, profile_image';


    protected $apiBaseUrl = 'https://kapi.kakao.com/v2/user/me';


    protected $authorizeUrl = 'https://kauth.kakao.com/oauth/authorize';


    protected $accessTokenUrl = 'https://kauth.kakao.com/oauth/token';


    protected $apiDocumentation = 'https://developers.kakao.com/docs/latest/ko/kakaologin/common';

    protected function initialize()
    {
        parent::initialize();

        $this->AuthorizeUrlParameters += [
            'access_type' => 'offline'
        ];

        if ($this->isRefreshTokenAvailable()) {
            $this->tokenRefreshParameters += [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret
            ];
        }
    }


    /**
     * {@inheritdoc}
     * See: https://developers.kakao.com/docs/latest/ko/kakaologin/rest-api#req-user-info
     * @return User\Profile
     * @throws UnexpectedApiResponseException
     * @throws \Hybridauth\Exception\HttpClientFailureException
     * @throws \Collection\Exception\HttpRequestFailedException
     * @throws \Hybridauth\Exception\InvalidAccessTokenException
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest($this->apiBaseUrl);

        $data = new Data\Collection($response);

        if (!$data->exists('id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $account = $data->filter('kakao_account');
        $profile = $account->filter('profile');

        $userProfile = new User\Profile();
        $userProfile->identifier = $data->get('id');
        $userProfile->firstName = null;
        $userProfile->displayName = $profile->get('nickname');
        $userProfile->profileURL = $profile->get('profile_image_url');
        $userProfile->photoURL = $profile->get('thumbnail_image_url');
        $userProfile->gender = $account->get('gender');
        $userProfile->email = $account->get('email');
        $userProfile->emailVerified = $account->get('is_email_verified') ? $account->get('email') : null;

        return $userProfile;
    }
}
