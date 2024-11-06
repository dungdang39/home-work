<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;

/**
 * 배열의 모든 요소에 콜백 함수를 적용
 * @param callable $fn 콜백 함수
 * @param mixed 
 * @return mixed 콜백 함수가 적용된 변수
 */
if (!function_exists('array_map_deep')) {
    function array_map_deep($fn, $array)
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $array[$key] = array_map_deep($fn, $value);
                } else {
                    $array[$key] = call_user_func($fn, $value);
                }
            }
        } else {
            $array = call_user_func($fn, $array);
        }

        return $array;
    }
}

/**
 * 랜덤 토큰 문자열 생성
 * @param int $length 토큰 길이
 * @return string 랜덤 토큰 문자열
 */
if (!function_exists('get_random_token_string')) {
    function getRandomTokenString(int $length = 6)
    {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes($length));
        }

        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $output = substr(str_shuffle($characters), 0, $length);

        return bin2hex($output);
    }
}


/**
 * 회원 아이디 해시 생성
 * - 해시값이 변경되지 않아야 하므로 hash_hmac 대신 hash 사용
 * @param string $mb_id 회원 아이디
 * @return string 해시값
 */
if (!function_exists('createMemberIdHash')) {
    function createMemberIdHash(string $mb_id): string
    {
        return hash('sha256', $mb_id);
    }
}

/**
 * 해시된 회원 아이디인지 확인
 * @param string $value 확인할 값
 * @return bool 해시된 회원 아이디이면 true
 */
if (!function_exists('isHashedMemberId')) {
    function isHashedMemberId(string $value): bool
    {
        return strlen($value) === 64; // SHA-256의 해시 길이가 64
    }
}

/**
 * 파일 이름만 가져오기
 * @param UploadedFileInterface $file 업로드 파일 객체
 * @return string 파일 이름
 */
if (!function_exists('getFilename')) {
    function getFilename(UploadedFileInterface $file): string
    {
        return pathinfo($file->getClientFilename(), PATHINFO_FILENAME);
    }
}

/**
 * 파일 확장자 가져오기
 * @param UploadedFileInterface $file 업로드 파일 객체
 * @return string 파일 확장자
 */
if (!function_exists('getExtension')) {
    function getExtension(UploadedFileInterface $file): string
    {
        return pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
    }
}

/**
 * 데이터 크기에 따른 단위 자동 추가
 *
 * @param int $bytes 데이터 크기 (byte)
 * @param int $decimals 소수점 자릿수 (default: 2)
 * @return string 크기 + 데이터 단위
 */
if (!function_exists('convertDataSizeUnit')) {
    function convertDataSizeUnit(int $bytes, int $decimals = 2)
    {
        $size   = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen((string)$bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . $size[$factor];
    }
}

/**
 * 패키지 버전 가져오기
 * @param string $packageName 패키지명
 * @return string|null 패키지 버전
 */
if (!function_exists('getPackageVersion')) {
    function getPackageVersion($package_name)
    {
        $composer_lock = json_decode(file_get_contents('./composer.lock'), true);
        foreach ($composer_lock['packages'] as $package) {
            if ($package['name'] === $package_name) {
                return $package['version'];
            }
        }
        return null;
    }
}

/**
 * 휴대폰번호의 숫자만 취한 후 중간에 하이픈(-)을 넣는다.
 * - common.lib.php 의 hyphen_hp_number() 함수
 * @param string $hp 휴대폰번호
 * @return string 하이픈이 추가된 휴대폰번호
 */
if (!function_exists('addHyphenPhoneNumber')) {

    function addHyphenPhoneNumber($hp): string
    {
        $hp = preg_replace("/[^0-9]/", "", $hp);
        return preg_replace("/([0-9]{3})([0-9]{3,4})([0-9]{4})$/", "\\1-\\2-\\3", $hp);
    }
}

/**
 * 이메일 주소 추출
 * - common.lib.php 의 get_email_address() 함수
 * @param string $email 이메일 주소
 * @return string 추출된 이메일 주소
 */
if (!function_exists('getEmailAddress')) {
    function getEmailAddress(string $email): string
    {
        preg_match("/[0-9a-z._-]+@[a-z0-9._-]{4,}/i", $email, $matches);

        return isset($matches[0]) ? $matches[0] : '';
    }
}


/**
 * 실제 IP 주소 가져오기
 * @param Request $request
 * @return string IP 주소
 */
if (!function_exists('getRealIp')) {
    function getRealIp(Request $request) {
        $server_params = $request->getServerParams();

        // X-Forwarded-For 헤더에 IP가 포함된 경우 확인 (프록시 사용 가능성)
        if (!empty($server_params['HTTP_X_FORWARDED_FOR'])) {
            $ip = $server_params['HTTP_X_FORWARDED_FOR'];
        } 
        // Cloudflare 등에서 사용하는 헤더 확인
        elseif (!empty($server_params['HTTP_CF_CONNECTING_IP'])) {
            $ip = $server_params['HTTP_CF_CONNECTING_IP'];
        }
        // 프록시 서버의 경우도 포함
        elseif (!empty($server_params['HTTP_CLIENT_IP'])) {
            $ip = $server_params['HTTP_CLIENT_IP'];
        }
        // 기본적으로 REMOTE_ADDR 사용
        else {
            $ip = $server_params['REMOTE_ADDR'];
        }

        // X-Forwarded-For가 여러 개의 IP를 가질 경우, 첫 번째 IP 추출
        if (strpos($ip, ',') !== false) {
            $ipArray = explode(',', $ip);
            $ip = trim($ipArray[0]);
        }

        return $ip;
    }
}