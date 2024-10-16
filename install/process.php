<?php
/**
 * 설치 진행 프로세스
 * @TODO: 설치 프로세스 확인 후 반영할 것
 * @TODO: 설치 처리 코드를 캡슐화
 */

use Core\AppConfig;
use Core\Database\Db;
use Install\InstallService;

require __DIR__ . '/../vendor/autoload.php';

define("_GNUBOARD_", true);
session_start();

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

include_once('../lib/pbkdf2.compat.php');
include_once('../lib/common.lib.php');

try {
    // 설치 정보 설정
    $app_config = AppConfig::getInstance();
    $form = $_SESSION['install_form'];
    $prefix = $form['table_prefix'];
    unset($_SESSION['install_form']);

    // 설치 정보 확인
    if (empty($form)) {
        throw new Exception("설치 정보가 없습니다.");
    }
    if (preg_match("/[^0-9a-z_]+/i", $prefix)) {
        throw new Exception("TABLE명 접두사는 영문자, 숫자, _ 만 입력하세요.");
    }
    if (preg_match("/[^0-9a-z_]+/i", $form['admin_id'])) {
        throw new Exception("관리자 아이디는 영문자, 숫자, _ 만 입력하세요.");
    }

    // 데이터베이스 연결
    $database_setting = [
        'driver' => 'mysql',
        'host' => $form['mysql_host'],
        'dbname' => $form['mysql_db'],
        'user' => $form['mysql_user'],
        'password' => $form['mysql_pass']
    ];
    Db::setInstance(new Db($database_setting));
    $install_service = new InstallService();

    // json파일의 기본 데이터 불러오기
    $json = file_get_contents('./data/default_value.json');
    $default_values = json_decode($json, true);

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

    $is_exists_table = Db::getInstance()->isTableExists($prefix . $default_values['config']['table']);

    // 테이블 생성
    if ($form['reinstall'] || $is_exists_table === false) {
        $install_service->createTable(Db::getInstance(), $prefix, './data/new-gnuboard.sql');
    }
    if ($form['shop_install']) {
        $install_service->createTable(Db::getInstance(), $prefix, './data/gnuboard5shop.sql');
    }

    send_message("create_table", "전체 테이블 생성 완료");

    // DB 초기 데이터 설정
    if ($form['reinstall'] || $is_exists_table === false) {
        // 기본환경설정
        $config = $default_values['config'];
        $config['fields']['cf_site_title'] = $app_config->get('APP_NAME');
        $config['fields']['cf_admin'] = $form['admin_id'];
        $config['fields']['cf_privacy_officer_name'] = $app_config->get('APP_NAME');
        $config['fields']['cf_privacy_officer_email'] = $form['admin_email'];
        Db::getInstance()->insert($prefix . $config['table'], $config['fields']);
        
        // 최고관리자
        $member = $default_values['member'];
        $member['fields']['mb_id'] = $form['admin_id'];
        $member['fields']['mb_id_hash'] = createMemberIdHash($form['admin_id']);
        $member['fields']['mb_password'] = password_hash($form['admin_pass'], PASSWORD_DEFAULT);
        $member['fields']['mb_name'] = $form['admin_name'];
        $member['fields']['mb_nick'] = $form['admin_name'];
        $member['fields']['mb_email'] = $form['admin_email'];
        $member['fields']['mb_email_verified_at'] = date('Y-m-d H:i:s');
        $member['fields']['mb_signup_ip'] = $_SERVER['REMOTE_ADDR'];
        Db::getInstance()->insert($prefix . $member['table'], $member['fields']);

        // 관리자 메뉴
        $admin_menus = $default_values['admin_menu'];
        $admin_menu_table = $prefix . $admin_menus['table'];
        insertAdminMenu($admin_menu_table, $admin_menus['values']);

        // Q&A 기본설정
        $qa_config = $default_values['qa_config'];
        Db::getInstance()->insert($prefix . $qa_config['table'], $qa_config['fields']);

        // 컨텐츠 설정
        $contents = $default_values['content'];
        foreach ($contents['values'] as $content) {
            Db::getInstance()->insert($prefix . $contents['table'], $content['fields']);
        }

        // API > 알림/메시징/메일 설정 
        $notifications = $default_values['notification'];
        $noti_table = $prefix . $notifications['table'];
        insertNotification($noti_table, $notifications['values']);

        /*
        // FAQ 설정
        $faq_category = $default_values['faq_category'];
        foreach ($faq_category['values'] as $category) {
            Db::getInstance()->insert($prefix . $faq_category['table'], $category['fields']);
        }
        // 게시판 그룹 설정
        $group = $default_values['group'];
        Db::getInstance()->insert($prefix . $group['table'], $group['fields']);

        // 게시판 설정
        $board = $default_values['board'];
        foreach ($default_values['board_list'] as $board) {
            $bo_skin = ($board['bo_table'] === 'gallery') ? 'gallery' : 'basic';
            $is_point_setting = in_array($board['bo_table'], array('gallery', 'qa'));

            $board['fields']['bo_table'] = $board['bo_table'];
            $board['fields']['gr_id'] = $group_fields['gr_id'];
            $board['fields']['bo_subject'] = $board['bo_subject'];
            $board['fields']['bo_read_point'] = $is_point_setting ? -1 : 0;
            $board['fields']['bo_write_point'] = $is_point_setting ? 5 : 0;
            $board['fields']['bo_comment_point'] = $is_point_setting ? 1 : 0;
            $board['fields']['bo_download_point'] = $is_point_setting ? -20 : 0;
            $board['fields']['bo_skin'] = $bo_skin;
            $board['fields']['bo_mobile_skin'] = $bo_skin;
            Db::getInstance()->insert($prefix . $board['table'], $board['fields']);

            // 게시판 테이블 생성
            $file = file("./data/sql_write.sql");
            $file = get_db_create_replace($file);
            $sql = implode("\n", $file);

            $create_table = $prefix . 'write_' . $board['bo_table'];

            // sql_board.sql 파일의 테이블명을 변환
            $source = array("/__TABLE_NAME__/", "/;/");
            $target = array($create_table, "");
            $sql = preg_replace($source, $target, $sql);
            Db::getInstance()->run($sql);
        }
        */
    }

    // 영카트 기본설정
    if ($form['shop_install']) {
        $shop_default = $default_values['shop_default'];
        Db::getInstance()->insert(
            $form['shop_table_prefix'] . $shop_default['table'],
            $shop_default['fields']
        );
    }

    send_message("create_data", "DB 데이터 설정 완료");

    // Data 디렉토리 생성
    $permission = $app_config->get('DIR_PERMISSION');
    $data_path = $app_config->get('BASE_PATH') . '/' . $app_config->get('DATA_DIR');
    $json = file_get_contents('./data/default_data_directory.json');
    $default_directory = json_decode($json, true);

    foreach ($default_directory['default'] as $dir) {
        $dir_path = $data_path . '/' . $dir;
        @mkdir($dir_path, $permission);
        @chmod($dir_path, $permission);
    }

    if ($form['shop_install']) {
        foreach ($default_directory['shop'] as $dir) {
            $dir_path = $data_path . '/' . $dir;
            @mkdir($dir_path, $permission);
            @chmod($dir_path, $permission);
        }
    }

    foreach ($default_values['board_list'] as $board) {
        $board_dir = $data_path . '/file/' . $board['bo_table'];
        @mkdir($board_dir, $permission);
        @chmod($board_dir, $permission);
    }

    // data 경로에 .htaccess 파일 생성
    $install_service->createHtaccessToDataDirectory($data_path);

    send_message("data_directory", "데이터 디렉토리 생성 완료");

    // .env 파일 생성
    $install_service->createEnvFile($form);

    // .htaccess 파일 생성
    $install_service->createHtaccess();

    if ($form['shop_install']) {
        @copy('./data/logo_img', $data_path . '/common/logo_img');
        @copy('./data/logo_img', $data_path . '/common/logo_img2');
        @copy('./data/mobile_logo_img', $data_path . '/common/mobile_logo_img');
        @copy('./data/mobile_logo_img', $data_path . '/common/mobile_logo_img2');
    }

    send_message('end', '설치 완료');
} catch (Exception $e) {
    send_message('error', '설치 실패 - ' . $e->getMessage());
    exit;
}


function send_message($id, $message)
{
    echo "id: $id\n";
    echo "data: $message\n\n";
    ob_flush();
    flush();
}

function insertAdminMenu($table, $menu, $parent_id = null) {
    $db = Db::getInstance();

    foreach ($menu as $item) {
        $children = $item['children'] ?? null;
        unset($item['children']);
        if (isset($parent_id)) {
            $item['am_parent_id'] = $parent_id;
        }
        $insert_id = $db->insert($table, $item);
    
        if (isset($children) && !empty($children)) {
            insertAdminMenu($table, $children, $insert_id);
        }
    }
}


function insertNotification($table, $noti, $parent_id = null) {
    $db = Db::getInstance();

    foreach ($noti as $item) {
        $children = $item['children'] ?? null;
        unset($item['children']);
        if (isset($parent_id)) {
            $item['notification_id'] = $parent_id;
            $db->insert($table . "_setting", $item);
        } else {
            $insert_id = $db->insert($table, $item);
        }
    
        if (isset($children) && !empty($children)) {
            insertNotification($table, $children, $insert_id);
        }
    }
}