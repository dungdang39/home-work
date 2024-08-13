<?php
/**
 * 설치 진행 프로세스
 * @TODO: 설치 프로세스 확인 후 반영하 것
 * @TODO: 설치 처리 코드를 캡슐화
 */
use Core\Database\Db;
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

    // 데이터베이스 연결
    Db::setInstance(new Db(
        'mysql',
        $form['mysql_host'],
        $form['mysql_db'],
        $form['mysql_user'],
        $form['mysql_pass']
    ));
    $install_service = new InstallService(Db::getInstance());

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

    $is_exists_table = Db::isTableExists($form['table_prefix'] . 'config');

    // 테이블 생성
    if ($form['reinstall'] || $is_exists_table === false) {
        $install_service->createTable($form['table_prefix'], './data/new-gnuboard.sql');
    }
    if ($form['shop_install']) {
        $install_service->createTable($form['table_prefix'], './data/gnuboard5shop.sql');
    }

    send_message("create_table", "전체 테이블 생성 완료");

    // DB 초기 데이터 설정
    if ($form['reinstall'] || $is_exists_table === false) {
        // 기본 이미지 확장자를 설정하고
        $image_extension = "gif|jpg|jpeg|png";
        // 서버에서 webp 를 지원하면 확장자를 추가한다.
        if (function_exists("imagewebp")) {
            $image_extension .= "|webp";
        }

        // config 테이블 설정
        $config_table = $form['table_prefix'] . $default_values['config']['table'];
        $config_fields = $default_values['config']['fields'];
        $config_fields['cf_title'] = G5_VERSION;
        $config_fields['cf_admin'] = $form['admin_id'];
        $config_fields['cf_admin_email'] = $form['admin_email'];
        $config_fields['cf_admin_email_name'] = G5_VERSION;
        $config_fields['cf_image_extension'] = $image_extension;
        Db::getInstance()->insert($config_table, $config_fields);

        // 최고관리자 설정
        $member_table = $form['table_prefix'] . $default_values['member']['table'];
        $member_fields = $default_values['member']['fields'];
        $member_fields['mb_id'] = $form['admin_id'];
        $member_fields['mb_password'] = get_encrypt_string($form['admin_pass']);
        $member_fields['mb_name'] = $form['admin_name'];
        $member_fields['mb_nick'] = $form['admin_name'];
        $member_fields['mb_email'] = $form['admin_email'];
        $member_fields['mb_nick_date'] = G5_TIME_YMDHIS;
        $member_fields['mb_email_certify'] = G5_TIME_YMDHIS;;
        $member_fields['mb_datetime'] = G5_TIME_YMDHIS;
        $member_fields['mb_ip'] = $_SERVER['REMOTE_ADDR'];
        Db::getInstance()->insert($member_table, $member_fields);

        // 관리자 메뉴 설정
        $admin_menu_table = $form['table_prefix'] . $default_values['admin_menu']['table'];
        foreach ($default_values['admin_menu']['values'] as $admin_menu) {
            $admin_menu_fields = $admin_menu['fields'];
            $admin_menu_fields['am_created_at'] = G5_TIME_YMDHIS;
            $insert_id = Db::getInstance()->insert($admin_menu_table, $admin_menu_fields);

            foreach ($admin_menu['children'] as $child_fields) {
                $child_fields['am_parent_id'] = $insert_id;
                $child_fields['am_created_at'] = G5_TIME_YMDHIS;
                Db::getInstance()->insert($admin_menu_table, $child_fields);
            }
        }

        // Q&A 설정
        $qa_config_table = $form['table_prefix'] . $default_values['qa_config']['table'];
        $qa_config_fields = $default_values['qa_config']['fields'];
        Db::getInstance()->insert($qa_config_table, $qa_config_fields);

        // 컨텐츠 설정
        $content_table = $form['table_prefix'] . $default_values['content']['table'];
        $content_values = $default_values['content']['values'];
        foreach ($content_values as $content) {
            $content_fields = $content['fields'];
            Db::getInstance()->insert($content_table, $content_fields);
        }

        // FAQ 설정
        $faq_master_table = $form['table_prefix'] . $default_values['faq_master']['table'];
        $faq_master_values = $default_values['faq_master']['values'];
        foreach ($faq_master_values as $faq_master) {
            $faq_master_fields = $faq_master['fields'];
            Db::getInstance()->insert($faq_master_table, $faq_master_fields);
        }

        // 게시판 그룹 설정
        $group_table = $form['table_prefix'] . $default_values['group']['table'];
        $group_fields = $default_values['group']['fields'];
        Db::getInstance()->insert($group_table, $group_fields);

        // 게시판 설정
        $board_config_table = $form['table_prefix'] . $default_values['board']['table'];
        $board_config_fields = $default_values['board']['fields'];
        foreach ($default_values['board_list'] as $board) {
            $bo_skin = ($board['bo_table'] === 'gallery') ? 'gallery' : 'basic';
            $is_point_setting = in_array($board['bo_table'], array('gallery', 'qa'));

            $board_config_fields['bo_table'] = $board['bo_table'];
            $board_config_fields['gr_id'] = $group_fields['gr_id'];
            $board_config_fields['bo_subject'] = $board['bo_subject'];
            $board_config_fields['bo_read_point'] = $is_point_setting ? -1 : 0;
            $board_config_fields['bo_write_point'] = $is_point_setting ? 5 : 0;
            $board_config_fields['bo_comment_point'] = $is_point_setting ? 1 : 0;
            $board_config_fields['bo_download_point'] = $is_point_setting ? -20 : 0;
            $board_config_fields['bo_skin'] = $bo_skin;
            $board_config_fields['bo_mobile_skin'] = $bo_skin;
            Db::getInstance()->insert($board_config_table, $board_config_fields);

            // 게시판 테이블 생성
            $file = file("./data/sql_write.sql");
            $file = get_db_create_replace($file);
            $sql = implode("\n", $file);

            $create_table = $form['table_prefix'] . 'write_' . $board['bo_table'];

            // sql_board.sql 파일의 테이블명을 변환
            $source = array("/__TABLE_NAME__/", "/;/");
            $target = array($create_table, "");
            $sql = preg_replace($source, $target, $sql);
            Db::getInstance()->run($sql);
        }

    }

    // 영카트 기본설정
    if ($form['shop_install']) {
        $shop_default_table = $form['shop_table_prefix'] . $default_values['shop_default']['table'];
        $shop_default_fields = $default_values['shop_default']['fields'];
        Db::getInstance()->insert($shop_default_table, $shop_default_fields);
    }

    send_message("create_data", "DB 데이터 설정 완료");

    // Data 디렉토리 생성
    $json = file_get_contents('./data/default_data_directory.json');
    $default_directory = json_decode($json, true);

    $data_path = G5_DATA_PATH;
    foreach ($default_directory['default'] as $dir) {
        $dir_path = $data_path . '/' . $dir;
        @mkdir($dir_path, G5_DIR_PERMISSION);
        @chmod($dir_path, G5_DIR_PERMISSION);
    }

    if ($form['shop_install']) {
        foreach ($default_directory['shop'] as $dir) {
            $dir_path = $data_path . '/' . $dir;
            @mkdir($dir_path, G5_DIR_PERMISSION);
            @chmod($dir_path, G5_DIR_PERMISSION);
        }
    }

    foreach ($default_values['board_list'] as $board) {
        $board_dir = $data_path . '/file/' . $board['bo_table'];
        @mkdir($board_dir, G5_DIR_PERMISSION);
        @chmod($board_dir, G5_DIR_PERMISSION);
    }

    send_message("data_directory", "데이터 디렉토리 생성 완료");

    // .env 파일 생성
    $key = get_random_token_string(16);
    $url = G5_URL;
    $env_file = fopen(G5_PATH . '/.env', 'w');
    $env_content = <<<EOD
    APP_ENV=production
    APP_DEBUG=false
    APP_KEY=base64:{$key}
    APP_URL={$url}

    DB_CONNECTION=mysql
    DB_HOST={$form['mysql_host']}
    DB_PORT=3306
    DB_DATABASE={$form['mysql_db']}
    DB_USERNAME={$form['mysql_user']}
    DB_PASSWORD={$form['mysql_pass']}
    DB_PREFIX={$form['table_prefix']}
    EOD;
    fwrite($env_file, $env_content);
    fclose($env_file);

    // data 디렉토리 및 하위 디렉토리에서는 .htaccess .htpasswd .php .phtml .html .htm .inc .cgi .pl .phar 파일을 실행할수 없게함.
    $f = fopen($data_path . '/.htaccess', 'w');
    $str = <<<EOD
    <FilesMatch "\.(htaccess|htpasswd|[Pp][Hh][Pp]|[Pp][Hh][Tt]|[Pp]?[Hh][Tt][Mm][Ll]?|[Ii][Nn][Cc]|[Cc][Gg][Ii]|[Pp][Ll]|[Pp][Hh][Aa][Rr])">
    Order allow,deny
    Deny from all
    </FilesMatch>
    RedirectMatch 403 /session/.*
    EOD;
    fwrite($f, $str);
    fclose($f);

    // .htaccess 파일 생성
    $htaccess_file = fopen(G5_PATH . '/.htaccess', 'w');
    $htaccess_content = <<<EOD
    RewriteEngine On

    # 설치 위치에 따라 RewriteBase 조정 (루트 설치시 '/' 사용)
    RewriteBase /new-gnuboard/

    # 모든 요청을 index.php로 전달
    RewriteRule ^index\.php$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /new-gnuboard/index.php [L]
    EOD;
    fwrite($htaccess_file, $htaccess_content);
    fclose($htaccess_file);

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
