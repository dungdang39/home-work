<?php

use Install\InstallService;
use Install\InstallValidateService;

require __DIR__ . '/../vendor/autoload.php';

// 헤더 설정
header('Expires: 0'); // rfc2616 - Section 14.21
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');  // HTTP/1.1
header('Pragma: no-cache');  // HTTP/1.0
header('Content-Type: text/html; charset=utf-8');
header('X-Robots-Tag: noindex');

// 서비스 및 템플릿 로드
$install = new InstallService();
$validate = new InstallValidateService();
$template = $install->loadTemplate();

// 정상이면 메인으로
// 설치 가능 여부 체크
$validate->checkInstallByStep2($template);

// 설치 정보 입력폼 출력
$response_data = [
    'ajax_token' => $validate->createAjaxToken()
];
echo $template->render('step2_form.html', $response_data);