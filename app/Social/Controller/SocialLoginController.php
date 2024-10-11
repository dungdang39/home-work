<?php

namespace App\Social\Controller;

use App\Social\SocialProfileService;
use App\Social\SocialService;
use Core\BaseController;
use Hybridauth\Hybridauth;
use Slim\Exception\HttpBadRequestException;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

class SocialLoginController extends BaseController
{
    private SocialService $service;
    private SocialProfileService $profile_service;

    public function __construct(
        SocialService $service,
        SocialProfileService $profile_service
    ) {
        $this->service = $service;
        $this->profile_service = $profile_service;
    }

    /**
     * 소셜 로그인 시도 
     */
    public function socialLogin(Request $request, Response $response, string $provider)
    {
        $login_member = $request->getAttribute('login_member');
        $is_login = $login_member ? true : false;

        $social = $this->service->getSocial($provider);
        if ($social['is_enabled'] === 0) {
            throw new HttpBadRequestException($request, '소셜 로그인이 비활성화되어 있습니다.');
        }

        // 소셜 로그인 설정 정보 가져오기
        $social_config = $this->service->getSocialConfig($request, $social);

        // Hybridauth 인증
        $hybridauth = new Hybridauth($social_config);
        $adapter = $hybridauth->authenticate($social['provider']);
        $profile = $adapter->getUserProfile();

        $social_profile = $this->profile_service->getProfile($provider, $profile->identifier);
        $is_exist = $social_profile ? true : false;

        // @todo 상황별 소셜 로그인 처리

        if ($is_login) {
            // 관리자페이지 > 메인페이지로 이동
            if ($is_exist) {
                return $this->redirectRoute($request, $response, 'admin.dashboard');
            }
            // 소셜 로그인 연결 페이지로 이동
            return $this->redirectRoute($request, $response, 'admin.member.manage.view', ['mb_id' => $login_member['mb_id']]);
        }

        if ($is_exist) {
            return $response->withJson([
                'message' => '로그인 처리',
                'profile' => $profile,
                'social_profile' => $social_profile,
            ]);
        }

        return $response->withJson(['message' => '소셜 회원가입 페이지 이동']);
    }

    /**
     * 소셜 로그인 콜백
     * - 소셜 로그인 콜백 URL로 호출됩니다.
     */
    public function socialLoginCallback(Request $request, Response $response, string $provider)
    {
        $social = $this->service->getSocial($provider);
        if ($social['is_enabled'] === 0) {
            throw new HttpBadRequestException($request, '소셜 로그인이 비활성화되어 있습니다.');
        }

        // 소셜 로그인 설정 정보 가져오기
        $social_config = $this->service->getSocialConfig($request, $social);

        // Hybridauth 인증
        $hybridauth = new Hybridauth($social_config);
        $adapter = $hybridauth->authenticate($social['provider']);

        return $response->withJson([
            'message' => '소셜 로그인 콜백',
            'profile' => $adapter->getUserProfile(),
            'is_connected' => $adapter->isConnected(),
        ]);
    }
}
