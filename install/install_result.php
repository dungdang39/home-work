<?php

use Install\InstallValidateService;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require __DIR__ . '/../vendor/autoload.php';

@set_time_limit(0);
header('Expires: 0'); // rfc2616 - Section 14.21
header('Last-Modified: ' .  gmdate('D, d M Y H:i:s') . ' GMT');
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

// 폼 데이터 체크
$form = [];
$form['mysql_host']  = isset($_POST['mysql_host']) ? safe_install_string_check($_POST['mysql_host']) : '';
$form['mysql_user']  = isset($_POST['mysql_user']) ? safe_install_string_check($_POST['mysql_user']) : '';
$form['mysql_pass']  = isset($_POST['mysql_pass']) ? safe_install_string_check($_POST['mysql_pass']) : '';
$form['mysql_db']    = isset($_POST['mysql_db']) ? safe_install_string_check($_POST['mysql_db']) : '';
$form['table_prefix']= isset($_POST['table_prefix']) ? safe_install_string_check($_POST['table_prefix']) : '';
$form['shop_table_prefix']= isset($_POST['shop_table_prefix']) ? safe_install_string_check($_POST['shop_table_prefix']) : 'yc_';
$form['reinstall'] = isset($_POST['reinstall']) ? (int) $_POST['reinstall'] : 0;
$form['shop_install'] = isset($_POST['shop_install']) ? (int) $_POST['shop_install'] : 0;
$form['admin_id']    = isset($_POST['admin_id']) ? $_POST['admin_id'] : '';
$form['admin_pass']  = isset($_POST['admin_pass']) ? $_POST['admin_pass'] : '';
$form['admin_name']  = isset($_POST['admin_name']) ? $_POST['admin_name'] : '';
$form['admin_email'] = isset($_POST['admin_email']) ? $_POST['admin_email'] : '';

session_start();
$_SESSION['install_form'] = $form;

// 설치 페이지 출력
$response_data = [
    "version" => G5_VERSION,
    "form" => $form,
];
echo $twig->render('install_result.html', $response_data);