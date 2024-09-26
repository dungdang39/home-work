<?php

use Psr\Http\Message\UploadedFileInterface;

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


if (!function_exists('getPackageVersion')) {
    /**
     * 패키지 버전 가져오기
     * @param string $packageName 패키지명
     * @return string|null 패키지 버전
     */
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