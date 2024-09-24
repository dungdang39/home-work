<?php

namespace App\Admin\Controller;

use App\Admin\Model\LoginRequest;
use App\Admin\Service\LoginService;
use App\Member\MemberService;
use Core\AppConfig;
use Exception;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;

class LoginController
{
    private LoginService $service;
    private MemberService $member_service;

    public function __construct(
        LoginService $service,
        MemberService $member_service
    ) {
        $this->service = $service;
        $this->member_service = $member_service;
    }

    public function adminLoginPage(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);

        // 이미 로그인 중이라면 리다이렉트
        // @todo 로그인 체크

        return $view->render($response, '/admin/login.html');
    }

    public function Login(Request $request, Response $response, LoginRequest $data): Response
    {
        try {
            // 데이터 검증 및 처리
            $login_id = $data->mb_id;

            // 로그인 체크
            $member = $this->member_service->fetchMemberById($login_id);

            // run_event('member_login_check_before', $login_id);
            // $is_need_not_password = run_replace('login_check_need_not_password', $is_social_password_check, $login_id, $mb_password, $mb, $is_social_login);
            $is_check_password = true;
            if (
                $is_check_password
                && (!$member || $this->service->checkPassword($data->mb_password, $member['mb_password']) === false)
            ) {
                // run_event('password_is_wrong', 'login', $member);
                throw new Exception('아이디 또는 패스워드가 일치하지 않습니다.');
            }

            if (($member['mb_intercept_date'] && $member['mb_intercept_date'] <= date("Ymd"))
                || ($member['mb_leave_date'] && $member['mb_leave_date'] <= date("Ymd"))
            ) {
                throw new Exception('탈퇴 또는 차단된 회원이므로 로그인하실 수 없습니다.');
            }

            // 메일인증 설정이 되어 있다면
            // if (is_use_email_certify() && !preg_match("/[1-9]/", $member['mb_email_certify'])) {
            //     throw new Exception("{$member['mb_email']} 메일로 메일인증을 받으셔야 로그인 가능합니다.");
            // }

            // 세션 생성 전 Hook
            // $is_social_login = false;
            // run_event('login_session_before', $member, $is_social_login);

            /*
            @include_once($member_skin_path.'/login_check.skin.php');

            if (! (defined('SKIP_SESSION_REGENERATE_ID') && SKIP_SESSION_REGENERATE_ID)) {
                session_regenerate_id(false);
                if (function_exists('session_start_samesite')) {
                    session_start_samesite();
                }
            }
            */

            // 회원아이디 세션 생성
            $_SESSION['ss_mb_id'] = $member['mb_id'];
            // FLASH XSS 공격에 대응하기 위하여 회원의 고유키를 생성해 놓는다. 관리자에서 검사함
            $this->service->set_member_key_session($member);

            /*
            // 회원의 토큰키를 세션에 저장한다. /common.php 에서 해당 회원의 토큰값을 검사한다.
            if (function_exists('update_auth_session_token')) {
                update_auth_session_token($member['mb_datetime']);
            }
            */

            /*
            // 회원 포인트 갱신
            if ($config['cf_use_point']) {
                $sum_point = get_point_sum($member['mb_id']);

                $sql = " update {$g5['member_table']} set mb_point = '$sum_point' where mb_id = '{$mb['mb_id']}' ";
                sql_query($sql);
            }
            */

            /*
            // 아이디 쿠키에 한달간 저장
            if (isset($auto_login) && $auto_login) {
                // 3.27
                // 자동로그인 ---------------------------
                // 쿠키 한달간 저장
                $key = md5($_SERVER['SERVER_ADDR'] . $_SERVER['SERVER_SOFTWARE'] . $_SERVER['HTTP_USER_AGENT'] . $member['mb_password']);
                set_cookie('ck_mb_id', $member['mb_id'], 86400 * 31);
                set_cookie('ck_auto', $key, 86400 * 31);
                // 자동로그인 end ---------------------------
            } else {
                set_cookie('ck_mb_id', '', 0);
                set_cookie('ck_auto', '', 0);
            }
            */

            //소셜 로그인 추가
            /*
            if(function_exists('social_login_success_after')){
                // 로그인 성공시 소셜 데이터를 기존의 데이터와 비교하여 바뀐 부분이 있으면 업데이트 합니다.
                $link = social_login_success_after($mb, $link);
                social_login_session_clear(1);
            }
            */

            // run_event('member_login_check', $member, $link, $is_social_login);

            // 관리자로 로그인시 DATA 폴더의 쓰기 권한이 있는지 체크합니다. 쓰기 권한이 없으면 로그인을 못합니다.
            $app_config = AppConfig::getInstance();
            $data_path = $app_config->get('BASE_PATH') . '/' . $app_config->get('DATA_DIR');
            if (is_dir($data_path . '/tmp/')) {
                $tmp_data_file = $data_path . '/tmp/tmp-write-test-' . time();
                $tmp_data_check = @fopen($tmp_data_file, 'w');
                if ($tmp_data_check) {
                    if (!@fwrite($tmp_data_check, "data forder write test")) {
                        $tmp_data_check = false;
                    }
                }
                if (is_resource($tmp_data_check)) @fclose($tmp_data_check);
                @unlink($tmp_data_file);

                if (!$tmp_data_check) {
                    // alert("data 폴더에 쓰기권한이 없거나 또는 웹하드 용량이 없는 경우\\n로그인을 못할수도 있으니, 용량 체크 및 쓰기 권한을 확인해 주세요.", $link);
                }
            }

            // 이전 패스워드 암호화 방식이라면 새로운 암호화 방식으로 업데이트
            if ($this->service->isOldPassword($member['mb_password'])) {
                $this->member_service->updatePasswordRehash($member['mb_id'], $data->mb_password);
            }
        } catch (Exception $e) {
            // alert($e->getMessage());
            throw new Exception($e->getMessage());
        }

        $routeContext = RouteContext::fromRequest($request);
        $redirect_url = $routeContext->getRouteParser()->urlFor('admin.dashboard');
        $query_params = $request->getQueryParams();
        if (!empty($query_params)) {
            $redirect_url .= '?' . http_build_query($query_params);
        }
        return $response->withHeader('Location', $redirect_url)->withStatus(302);
    }

    public function Logout(Request $request, Response $response): Response
    {
        /*
        if (function_exists('social_provider_logout')) {
            social_provider_logout();
        }
        */

        // 세션 초기화
        session_unset();
        session_destroy();

        // 쿠키 초기화
        setcookie('ck_mb_id', '', 0);
        setcookie('ck_auto', '', 0);

        // 로그인 페이지로 리다이렉트
        $routeContext = RouteContext::fromRequest($request);
        $redirect_url = $routeContext->getRouteParser()->urlFor('admin.login');

        // run_event('admin_logout', $redirect_url);

        return $response->withHeader('Location', $redirect_url)->withStatus(302);
    }
}