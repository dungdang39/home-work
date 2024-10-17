<?php

use Install\InstallService;
use Install\InstallValidateService;

require __DIR__ . '/../vendor/autoload.php';

set_time_limit(0);
session_start();

// 헤더 설정
header('Expires: 0'); // rfc2616 - Section 14.21
header('Last-Modified: ' .  gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate max-age=0');  // HTTP/1.1
header('Pragma: no-cache');  // HTTP/1.0
header('Content-Type: text/html; charset=utf-8');
header('X-Robots-Tag: noindex');

// 서비스 및 템플릿 로드
$install = new InstallService();
$template = $install->loadTemplate();
$validate = new InstallValidateService();

// 설치 가능 여부 체크
$error = $validate->validateInstall($template);
if ($error) {
    echo $error;
    exit;
}

// 폼 데이터 체크
$form = [];
$form['mysql_host']  = $validate->validateInstallInput($_POST['mysql_host']);
$form['mysql_user']  = $validate->validateInstallInput($_POST['mysql_user']);
$form['mysql_pass']  = $validate->validateInstallInput($_POST['mysql_pass']);
$form['mysql_db']    = $validate->validateInstallInput($_POST['mysql_db']);
$form['table_prefix']= $validate->validateInstallInput($_POST['table_prefix']);
// $form['shop_table_prefix']= isset($_POST['shop_table_prefix']) ? safe_install_string_check($_POST['shop_table_prefix']) : 'yc_';
$form['reinstall'] = isset($_POST['reinstall']) ? (int) $_POST['reinstall'] : 0;
$form['shop_install'] = isset($_POST['shop_install']) ? (int) $_POST['shop_install'] : 0;
$form['admin_id']    = isset($_POST['admin_id']) ? $_POST['admin_id'] : '';
$form['admin_pass']  = isset($_POST['admin_pass']) ? $_POST['admin_pass'] : '';
$form['admin_name']  = isset($_POST['admin_name']) ? $_POST['admin_name'] : '';
$form['admin_email'] = isset($_POST['admin_email']) ? $_POST['admin_email'] : '';

$_SESSION['install_form'] = $form;

// 설치 페이지 출력
$response_data = [
    "form" => $form,
];
echo $template->render('install_result.html', $response_data);