<?php

namespace Install;

use Install\InstallService;
use Install\InstallValidateService;

require __DIR__ . '/../vendor/autoload.php';

// 헤더 설정
header('Content-Type: text/html; charset=utf-8');
header('X-Robots-Tag: noindex');

// 서비스 및 템플릿 로드
$install = new InstallService();
$validate = new InstallValidateService();
$template = $install->loadTemplate();

// 설치 가능 여부 체크
$validate->checkInstallByStep1($template);

// GD 라이브러리 체크
if (!$validate->isGdLibraryExists()) {
    echo $template->render('error/gd_library.html');
}

echo $template->render('index.html');