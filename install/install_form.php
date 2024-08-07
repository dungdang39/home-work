<?php

use League\Plates\Engine;
use Install\InstallService;

require __DIR__ . '/../vendor/autoload.php';

// 헤더 설정
header('Expires: 0'); // rfc2616 - Section 14.21
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0
@header('Content-Type: text/html; charset=utf-8');
@header('X-Robots-Tag: noindex');

$g5_path['path'] = '..';
include_once('../config.php');
include_once('./install.function.php');

$templates = new Engine('./template');
$install_service = new InstallService($templates);

// 설치 가능 여부 체크
$error = $install_service->validateInstall();
if ($error) {
    echo $error;
    exit;
}
// 라이센스 동의 체크
$agree = isset($_POST['agree']) ? $_POST['agree'] : '';
if ($install_service->checkLicenseAgree($agree)) {
    echo $templates->render("error/license_agree", ["version" => G5_VERSION]);
    exit;
}

// 설치 정보 입력폼 출력
$response_data = [
    "version" => G5_VERSION,
    "ajax_token" => make_ajax_token(),
];
echo $templates->render('install_form', $response_data);