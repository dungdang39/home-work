<?php

use Install\InstallValidateService;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

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

$loader = new FilesystemLoader(dirname(__DIR__, 1) . "/install/template/");
$twig = new Environment($loader);
$root_path = g5_root_path(false, 1);
$twig->addGlobal('base_url', $root_path['url']);
$validate_service = new InstallValidateService($twig);

// 설치 가능 여부 체크
$error = $validate_service->validateInstall();
if ($error) {
    echo $error;
    exit;
}
// 라이센스 동의 체크
$agree = isset($_POST['agree']) ? $_POST['agree'] : '';
if ($validate_service->checkLicenseAgree($agree)) {
    echo $twig->render("error/license_agree.html", ["version" => G5_VERSION]);
    exit;
}

// 설치 정보 입력폼 출력
$response_data = [
    "version" => G5_VERSION,
    "ajax_token" => make_ajax_token(),
];
echo $twig->render('install_form.html', $response_data);