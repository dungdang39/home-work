<?php

use Core\Database\Db;
use League\Plates\Engine;
use Install\InstallService;

require __DIR__ . '/../vendor/autoload.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

$g5_path['path'] = '..';
include_once('../config.php');
include_once('../lib/common.lib.php');
include_once('./install.function.php');    // 인스톨 과정 함수 모음

include_once('../lib/hook.lib.php');    // hook 함수 파일
include_once('../lib/get_data.lib.php');
include_once('../lib/uri.lib.php');    // URL 함수 파일
include_once('../lib/cache.lib.php');

$templates = new Engine('./template');
$install_service = new InstallService($templates);

try {
    session_start();
    $form = $_SESSION['install_form'];
    unset($_SESSION['install_form']);

    if (empty($form)) {
        throw new Exception("설치 정보가 없습니다.");
    }
    if (preg_match("/[^0-9a-z_]+/i", $form['table_prefix'])) {
        throw new Exception("TABLE명 접두사는 영문자, 숫자, _ 만 입력하세요.");
    }
    if (preg_match("/[^0-9a-z_]+/i", $form['admin_id'])) {
        throw new Exception("관리자 아이디는 영문자, 숫자, _ 만 입력하세요.");
    }

    // TODO: 위치 이동 필요
    $tmp_bo_table = array("notice", "qa", "free", "gallery");

    // 데이터베이스 연결
    Db::setInstance(new Db(
        'mysql',
        $form['mysql_host'],
        $form['mysql_db'],
        $form['mysql_user'],
        $form['mysql_pass']
    ));

    // $mysql_set_mode = 'false';
    // sql_set_charset(G5_DB_CHARSET, $dblink);
    // $result = sql_query(" SELECT @@sql_mode as mode ", true, $dblink);
    // $row = sql_fetch_array($result);
    // if($row['mode']) {
    //     sql_query("SET SESSION sql_mode = ''", true, $dblink);
    //     $mysql_set_mode = 'true';
    // }
    // unset($result);
    // unset($row);

    // 그누보드5 재설치에 체크하였거나 그누보드5가 설치되어 있지 않다면
    $is_exists_table = Db::isTableExists($form['table_prefix'] . 'config');
    if ($form['reinstall'] || $is_exists_table === false) {
        // 테이블 생성 ------------------------------------
        $file = implode('', file('./gnuboard5_test.sql'));
        eval("\$file = \"$file\";");

        $file = preg_replace('/^--.*$/m', '', $file);
        $file = preg_replace('/`g5_([^`]+`)/', '`' . $form['table_prefix'] . '$1', $file);
        $f = explode(';', $file);
        for ($i = 0; $i < count($f); $i++) {
            if (trim($f[$i]) == '') {
                continue;
            }

            $sql = get_db_create_replace($f[$i]);
            Db::getInstance()->run($sql);
        }

        // 쇼핑몰 테이블 생성 -----------------------------
        // if ($g5_shop_install) {
        //     $file = implode('', file('./gnuboard5shop.sql'));

        //     $file = preg_replace('/^--.*$/m', '', $file);
        //     $file = preg_replace('/`g5_shop_([^`]+`)/', '`' . $g5_shop_prefix . '$1', $file);
        //     $f = explode(';', $file);
        //     for ($i = 0; $i < count($f); $i++) {
        //         if (trim($f[$i]) == '') {
        //             continue;
        //         }

        //         $sql = get_db_create_replace($f[$i]);
        //         sql_query($sql, true, $dblink);
        //     }
        // }
        // 테이블 생성 ------------------------------------
        send_message("creat table", "전체 테이블 생성 완료");
    }
} catch (Exception $e) {
    send_message('error', '설치 실패 - ' . $e->getMessage());
    exit;
}


send_message('end', '설치 완료');


function send_message($id, $message)
{
    echo "id: $id\n";
    echo "data: $message\n\n";
    ob_flush();
    flush();
}
