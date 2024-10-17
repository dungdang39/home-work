<?php

use Core\Database\Db;
use Install\InstallValidateService;

require __DIR__ . '/../vendor/autoload.php';

$validate = new InstallValidateService();

if ($validate->isInstalled()) {
    die($validate->jsonResponse('프로그램이 이미 설치되어 있습니다.'));
}

if (isset($_POST['table_prefix']) && preg_match("/[^0-9a-z_]+/i", $_POST['table_prefix'])) {
    die($validate->jsonResponse('TABLE명 접두사는 영문자, 숫자, _ 만 입력하세요.'));
}

$mysql_host = $validate->validateInstallInput($_POST['mysql_host']);
$mysql_user = $validate->validateInstallInput($_POST['mysql_user']);
$mysql_pass = $validate->validateInstallInput($_POST['mysql_pass']);
$mysql_db = $validate->validateInstallInput($_POST['mysql_db']);
$table_prefix = $validate->validateInstallInput(preg_replace('/[^a-zA-Z0-9_]/', '_', $_POST['table_prefix']));
$ajax_token = isset($_POST['ajax_token']) ? $_POST['ajax_token'] : '';
$bool_ajax_token = ($validate->createAjaxToken() === $ajax_token);

if (!($mysql_host && $mysql_user && $mysql_pass && $mysql_db && $table_prefix && $bool_ajax_token)) {
    die($validate->jsonResponse('잘못된 요청입니다.'));
}

// 데이터베이스 연결 테스트
$database_setting = [
    'driver' => 'mysql',
    'host' => $mysql_host,
    'dbname' => $mysql_db,
    'user' => $mysql_user,
    'password' => $mysql_pass
];
$result = Db::testConnection($database_setting);
if (!$result['success']) {
    die($validate->jsonResponse('MySQL 연결에 실패하였습니다. ' . $result['message']));
}
Db::setInstance(new Db($database_setting));

// 테이블 존재 여부 체크
if (Db::getInstance()->isTableExists($table_prefix . 'config')) {
    die($validate->jsonResponse('이미 설치된 데이터베이스가 존재합니다. 계속하시겠습니까?', 'confirm'));
}

die($validate->jsonResponse('성공', 'success'));
