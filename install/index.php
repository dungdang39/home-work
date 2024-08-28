<?php

use Install\InstallService;
use Install\InstallValidateService;

require __DIR__ . '/../vendor/autoload.php';

// 헤더 설정
@header('Content-Type: text/html; charset=utf-8');
@header('X-Robots-Tag: noindex');

// 서비스 및 템플릿 로드
$install_service = new InstallService();
$template = $install_service->loadTemplate();
$validate_service = new InstallValidateService($template);

// 설치 가능 여부 체크
$error = $validate_service->validateInstall();
if ($error) {
    echo $error;
    exit;
}
// GD 라이브러리 체크
if (!$validate_service->isGdLibraryExists()) {
    echo $template->render("error/gd_library.html");
}

// 라이센스 동의 폼 출력
$license = $install_service->getLicense();
$response_data = [
    "license" => $license,
];
echo $template->render('index.html', $response_data);