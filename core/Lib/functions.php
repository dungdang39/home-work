<?php

use Psr\Http\Message\UploadedFileInterface;

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
    function getPackageVersion($package_name) {
        $composer_lock = json_decode(file_get_contents('./composer.lock'), true);
        foreach ($composer_lock['packages'] as $package) {
            if ($package['name'] === $package_name) {
                return $package['version'];
            }
        }
        return null;
    }
}