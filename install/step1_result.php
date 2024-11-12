<?php

namespace Install;

use Core\Database\Db;
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

// 입력 값 체크
if (isset($_POST['db_prefix']) && preg_match("/[^0-9a-z_]+/i", $_POST['db_prefix'])) {
    echo $template->render('error/basic.html', ['message', 'TABLE명 접두사는 영문자, 숫자, _ 만 입력하세요.']);
    exit;
}
$db_host = $validate->validateInstallInput($_POST['db_host']);
$db_user = $validate->validateInstallInput($_POST['db_user']);
$db_password = $validate->validateInstallInput($_POST['db_password']);
$db_dbname = $validate->validateInstallInput($_POST['db_dbname']);
$db_prefix = $validate->validateInstallInput(preg_replace('/[^a-zA-Z0-9_]/', '_', $_POST['db_prefix']));
$ajax_token = isset($_POST['ajax_token']) ? $_POST['ajax_token'] : '';
$bool_ajax_token = ($validate->createAjaxToken() === $ajax_token);

if (!($db_host && $db_user && $db_password && $db_dbname && $db_prefix && $bool_ajax_token)) {
    echo $template->render('error/basic.html', ['message', '잘못된 요청입니다. 입력 값을 다시 확인해주세요.']);
    exit;
}

// 데이터베이스 연결 테스트
$database_setting = [
    'connection' => 'mysql',
    'host' => $_POST['db_host'],
    'dbname' => $_POST['db_dbname'],
    'port' => 3306,
    'user' => $_POST['db_user'],
    'password' => $_POST['db_password']
];
$result = Db::testConnection($database_setting);
if (!$result['success']) {
    echo $template->render('error/db_connect.html');
    exit;
}

// .env 파일 생성
$install->createEnvFile([
    'db_host' => $_POST['db_host'],
    'db_dbname' => $_POST['db_dbname'],
    'db_user' => $_POST['db_user'],
    'db_password' => $_POST['db_password'],
    'db_prefix' => $_POST['db_prefix']
]);

// .htaccess 파일 생성
$install->createHtaccess();

// step1 결과 출력
echo $template->render('step1_result.html');