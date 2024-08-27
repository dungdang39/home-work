<?php

use Install\InstallValidateService;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

require __DIR__ . '/../vendor/autoload.php';

// 헤더 설정
@header('Content-Type: text/html; charset=utf-8');
@header('X-Robots-Tag: noindex');

$g5_path['path'] = '..';
include_once('../config.php');

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
// GD 라이브러리 체크
if (!$validate_service->isGdLibraryExists()) {
    echo $twig->render("error/gd_library.html", ["version" => G5_VERSION]);
}

// 라이센스 동의 폼 출력
$license = file_get_contents('../LICENSE.txt');
$response_data = [
    "version" => G5_VERSION,
    "license" => $license,
];
echo $twig->render('index.html', $response_data);