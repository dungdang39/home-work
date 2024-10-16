<?php

namespace App\Base\Controller\Admin;

use App\Base\Service\LoginService;
use App\Base\Service\MemberService;
use App\Base\Service\SocialProfileService;
use App\Base\Service\SocialService;
use Core\BaseController;
use Hybridauth\Hybridauth;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

class SocialLoginController extends BaseController
{
    private LoginService $login_service;
    private MemberService $member_service;
    private SocialService $service;
    private SocialProfileService $profile_service;

    public function __construct(
        LoginService $login_service,
        MemberService $member_service,
        SocialService $service,
        SocialProfileService $profile_service
    ) {
        $this->login_service = $login_service;
        $this->member_service = $member_service;
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
            if ($is_exist) {
                return $this->redirectRoute($request, $response, 'admin.dashboard');
            }
            return $this->redirectRoute($request, $response, 'admin.member.manage.view', ['mb_id' => $login_member['mb_id']]);
        }

        if (!$is_exist) {
            throw new HttpForbiddenException($request, '가입된 관리자 계정이 없습니다.');
        }

        $this->login_service->login($social_profile['mb_id']);

        return $this->redirectRoute($request, $response, 'admin.dashboard');

        // return $response->withJson([
        //     'message' => '로그인 처리',
        //     'profile' => $profile,
        //     'social_profile' => $social_profile,
        // ]);
    }

    /**
     * 소셜 로그인 콜백
     * - 소셜 로그인 콜백 URL로 호출됩니다.
     */
    public function socialLoginCallback(Request $request, Response $response, string $provider)
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
            if ($is_exist) {
                return $this->redirectRoute($request, $response, 'admin.dashboard');
            }
            return $this->redirectRoute($request, $response, 'admin.member.manage.view', ['mb_id' => $login_member['mb_id']]);
        }

        if (!$is_exist) {
            throw new HttpForbiddenException($request, '가입된 관리자 계정이 없습니다.');
        }

        $this->login_service->login($social_profile['mb_id']);

        return $this->redirectRoute($request, $response, 'admin.dashboard');
    }

    public function linkSocial(Request $request, Response $response)
    {
        // @todo
    }
    
    /**
     * 소셜 연결 끊기
     */
    public function unlinkSocial(Request $request, Response $response, string $mb_id, string $provider): Response
    {
        $member = $this->member_service->getMember($mb_id);

        $this->profile_service->unlink($provider, $member['mb_id']);

        return $response->withJson([
            'message' => '소셜 연결이 해제되었습니다.',
        ], 200);
    }
}
