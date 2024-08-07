<?php

use League\Plates\Engine;
use Install\InstallService;

require __DIR__ . '/../vendor/autoload.php';

// 헤더 설정
@header('Content-Type: text/html; charset=utf-8');
@header('X-Robots-Tag: noindex');

$g5_path['path'] = '..';
include_once('../config.php');

$templates = new Engine('./template');
$install_service = new InstallService($templates);

// 설치 가능 여부 체크
$error = $install_service->validateInstall();
if ($error) {
    echo $error;
    exit;
}
// GD 라이브러리 체크
if (!$install_service->isGdLibraryExists()) {
    echo $templates->render("error/gd_library", ["version" => G5_VERSION]);
}

// 라이센스 동의 폼 출력
$license = file_get_contents('../LICENSE.txt');
$response_data = [
    "version" => G5_VERSION,
    "license" => $license,
];
echo $templates->render('index', $response_data);