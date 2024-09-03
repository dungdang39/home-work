<?php

use Core\Database\Db;
use Install\InstallValidateService;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require __DIR__ . '/../vendor/autoload.php';

include_once('./install.function.php');    // 인스톨 과정 함수 모음
include_once('../lib/common.lib.php');    // 공통 라이브러리
include_once('../lib/hook.lib.php');    // hook 함수 파일
include_once('../lib/get_data.lib.php');    // 데이터 가져오는 함수 모음

$loader = new FilesystemLoader(dirname(__DIR__, 1) . "/install/template/");
$twig = new Environment($loader);
$validate_service = new InstallValidateService($twig);

if ($validate_service->isInstalled()) {
    die(install_json_msg('프로그램이 이미 설치되어 있습니다.'));
}

if (isset($_POST['table_prefix']) && preg_match("/[^0-9a-z_]+/i", $_POST['table_prefix'])) {
    die(install_json_msg('TABLE명 접두사는 영문자, 숫자, _ 만 입력하세요.'));
}

$mysql_host  = isset($_POST['mysql_host']) ? safe_install_string_check($_POST['mysql_host'], 'json') : '';
$mysql_user  = isset($_POST['mysql_user']) ? safe_install_string_check($_POST['mysql_user'], 'json') : '';
$mysql_pass  = isset($_POST['mysql_pass']) ? safe_install_string_check($_POST['mysql_pass'], 'json') : '';
$mysql_db    = isset($_POST['mysql_db']) ? safe_install_string_check($_POST['mysql_db'], 'json') : '';
$table_prefix= isset($_POST['table_prefix']) ? safe_install_string_check(preg_replace('/[^a-zA-Z0-9_]/', '_', $_POST['table_prefix'])) : '';
$ajax_token = isset($_POST['ajax_token']) ? $_POST['ajax_token'] : '';
$bool_ajax_token = (make_ajax_token() === $ajax_token);

if (!($mysql_host && $mysql_user && $mysql_pass && $mysql_db && $table_prefix && $bool_ajax_token)) {
    die(install_json_msg('잘못된 요청입니다.'));
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
    die(install_json_msg('MySQL 연결에 실패하였습니다. ' . $result['message']));
}
Db::setInstance(new Db($database_setting));

// 테이블 존재 여부 체크
if (Db::getInstance()->isTableExists($table_prefix . 'config')) {
    die(install_json_msg('이미 설치된 데이터베이스가 존재합니다. 계속하시겠습니까?', 'exists'));
}

die(install_json_msg('ok', 'success'));
