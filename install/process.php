<?php

/**
 * 설치 진행 프로세스
 * @TODO: 설치 프로세스 확인 후 반영할 것
 * @TODO: 설치 처리 코드를 캡슐화
 */

use App\Base\Service\AdminMenuService;
use App\Base\Service\BoardService;
use App\Base\Service\ConfigService;
use App\Base\Service\ContentService;
use App\Base\Service\FaqService;
use App\Base\Service\MemberService;
use App\Base\Service\NotificationService;
use App\Base\Service\ThemeService;
use Core\AppConfig;
use Core\Database\Db;
use Install\InstallService;

require __DIR__ . '/../vendor/autoload.php';

session_start();

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

try {
    // 설치 정보 설정
    $app_config = AppConfig::getInstance();
    $install_service = new InstallService();

    $form = $_SESSION['install_form'];
    $prefix = $form['table_prefix'];
    unset($_SESSION['install_form']);

    // 설치 정보 확인
    if (empty($form)) {
        throw new Exception('설치 정보가 없습니다.');
    }
    if (preg_match("/[^0-9a-z_]+/i", $prefix)) {
        throw new Exception('TABLE명 접두사는 영문자, 숫자, _ 만 입력하세요.');
    }
    if (preg_match("/[^0-9a-z_]+/i", $form['admin_id'])) {
        throw new Exception('관리자 아이디는 영문자, 숫자, _ 만 입력하세요.');
    }

    // 데이터베이스 연결
    $database_setting = [
        'connection' => 'mysql',
        'host' => $form['mysql_host'],
        'dbname' => $form['mysql_db'],
        'port' => 3306,
        'user' => $form['mysql_user'],
        'password' => $form['mysql_pass']
    ];
    Db::setInstance(new Db($database_setting));
    $db = Db::getInstance();

    $is_exists_table = $db->isTableExists($prefix . ConfigService::TABLE_NAME);

    /**
     * 테이블 생성
     */
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
    if ($form['reinstall'] || $is_exists_table === false) {
        $install_service->createTable($db, $prefix, './data/new-gnuboard.sql');
    }

    send_message('create_table', '전체 테이블 생성 완료');

    /**
     * 초기 데이터 생성
     */
    if ($form['reinstall'] || $is_exists_table === false) {

        $db->getPdo()->beginTransaction();

        // 기본 데이터 파일 불러오기
        $default_values = json_decode(file_get_contents('./data/default_value.json'), true);

        createConfigData($db, $prefix, $form, $default_values['config']);
        createAdminData($db, $prefix, $form);
        createAdminMenuData($db, $prefix, $default_values['admin_menu']);
        createContentData($db, $prefix, $default_values['content']);
        createNotificationData($db, $prefix, $default_values['notification']);
        createFaqCategoryData($db, $prefix, $default_values['faq_category']);
        // createQaConfigData($db, $prefix, $default_values['qa_config']);
        createBoardData($db, $prefix, $default_values['board']);

        $db->getPdo()->commit();
    }

    send_message('create_data', 'DB 데이터 설정 완료');

    /**
     * Data 디렉토리 설정
     */
    $permission = $app_config->get('DIR_PERMISSION');
    $data_path = $app_config->get('BASE_PATH') . '/' . $app_config->get('DATA_DIR');
    $json = file_get_contents('./data/default_data_directory.json');
    $default_directory = json_decode($json, true);

    foreach ($default_directory['default'] as $dir) {
        $dir_path = $data_path . '/' . $dir;
        @mkdir($dir_path, $permission);
        @chmod($dir_path, $permission);
    }

    // data 경로에 .htaccess 파일 생성
    $install_service->createHtaccessToDataDirectory($data_path);

    send_message('data_directory', '데이터 디렉토리 생성 완료');

    /**
     * 기타 파일 생성
     */
    $install_service->createEnvFile($form);
    $install_service->createHtaccess();

    send_message('end', '설치 완료');
} catch (Exception $e) {
    send_message('error', '설치 실패 - ' . $e->getMessage());
    exit;
}

/**
 * 메시지 전송
 * @param string $id 메시지 ID
 * @param string $message 메시지
 * @return void
 */
function send_message(string $id, string $message): void
{
    echo "id: $id\n";
    echo "data: $message\n\n";
    ob_flush();
    flush();
}

/**
 * 기본환경설정 데이터 생성
 * @param Db $db 데이터베이스 객체
 * @param string $prefix 테이블 접두사
 * @param array $form 폼 데이터
 * @param array $values 기본환경설정 데이터
 * @return void
 */
function createConfigData(Db $db, string $prefix, array $form, array $values): void
{
    $table = $prefix . ConfigService::TABLE_NAME;

    $db->insert($table, ['scope' => 'config', 'name' => 'site_title', 'value' => $form['site_title']]);
    $db->insert($table, ['scope' => 'config', 'name' => 'super_admin', 'value' => $form['admin_id']]);
    $db->insert($table, ['scope' => 'config', 'name' => 'privacy_officer_name', 'value' => $form['admin_name']]);
    $db->insert($table, ['scope' => 'config', 'name' => 'privacy_officer_email', 'value' => $form['admin_email']]);
    $db->insert($table, ['scope' => 'config', 'name' => 'mail_address', 'value' => $form['admin_email']]);
    $db->insert($table, ['scope' => 'config', 'name' => 'mail_name', 'value' => $form['site_title'] ?? $form['admin_name']]);
    $db->insert($table, ['scope' => 'design', 'name' => 'theme', 'value' => ThemeService::DEFAULT_THEME]);

    foreach ($values as $value) {
        $db->insert($table, $value);
    }
}

/**
 * 최고관리자 데이터 생성
 * @param Db $db 데이터베이스 객체
 * @param string $prefix 테이블 접두사
 * @param array $form 폼 데이터
 * @return void
 */
function createAdminData(Db $db, string $prefix, array $form): void
{
    $table = $prefix . MemberService::TABLE_NAME;

    $db->insert($table, [
        'mb_id' => $form['admin_id'],
        'mb_id_hash' => createMemberIdHash($form['admin_id']),
        'mb_password' => password_hash($form['admin_pass'], PASSWORD_DEFAULT),
        'mb_name' => $form['admin_name'],
        'mb_nick' => $form['admin_name'],
        'mb_email' => $form['admin_email'],
        'mb_email_verified_at' => date('Y-m-d H:i:s'),
        'mb_level' => 10,
        'mb_signup_ip' => filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP),
    ]);
}

/**
 * 관리자페이지 메뉴 데이터 생성
 * @param Db $db 데이터베이스 객체
 * @param string $prefix 테이블 접두사
 * @param array $menus 메뉴 데이터
 * @param int|null $parent_id 부모 메뉴 ID
 * @return void
 */
function createAdminMenuData(Db $db, string $prefix, array $menus, ?int $parent_id = null): void
{
    $table = $prefix . AdminMenuService::TABLE_NAME;

    foreach ($menus as $menu) {
        $submenu = $menu['submenu'] ?? null;
        unset($menu['submenu']);

        if ($parent_id !== null) {
            $menu = array_merge($menu, ['am_parent_id' => $parent_id]);
        }

        $insert_id = $db->insert($table, $menu);

        if ($submenu) {
            createAdminMenuData($db, $prefix, $submenu, $insert_id);
        }
    }
}

/**
 * 컨텐츠 데이터 생성
 * @param Db $db 데이터베이스 객체
 * @param string $prefix 테이블 접두사
 * @param array $contents 컨텐츠 데이터
 * @return void
 */
function createContentData(Db $db, string $prefix, array $contents)
{
    $table = $prefix . ContentService::TABLE_NAME;

    foreach ($contents as $content) {
        $db->insert($table, $content);
    }
}

/**
 * API > 알림/메시징/메일 설정 데이터 생성
 * @param Db $db 데이터베이스 객체
 * @param string $prefix 테이블 접두사
 * @param array $datas 알림 설정 데이터
 * @param int|null $notification_id 부모 알림 ID
 * @return void
 */
function createNotificationData(Db $db, string $prefix, array $datas, ?int $notification_id = null): void
{
    $table = $prefix . NotificationService::TABLE_NAME;
    $setting_table = $prefix . NotificationService::SETTING_TABLE_NAME;

    foreach ($datas as $data) {
        $settings = $data['settings'] ?? null;
        unset($data['settings']);

        if ($notification_id !== null) {
            $data = array_merge($data, ['notification_id' => $notification_id]);
            $db->insert($setting_table, $data);
        } else {
            $insert_id = $db->insert($table, $data);
            if ($settings) {
                createNotificationData($db, $prefix, $settings, $insert_id);
            }
        }
    }
}

/**
 * FAQ 카테고리 데이터 생성
 * @param Db $db 데이터베이스 객체
 * @param string $prefix 테이블 접두사
 * @param array $categories FAQ 카테고리 데이터
 * @return void
 */
function createFaqCategoryData(Db $db, string $prefix, array $categories): void
{
    $table = $prefix . FaqService::CATEGORY_TABLE_NAME;
    foreach ($categories as $category) {
        $db->insert($table, $category);
    }
}

/**
 * 게시판 데이터 생성
 * @param Db $db 데이터베이스 객체
 * @param string $prefix 테이블 접두사
 * @param array $boards 게시판 데이터
 * @return void
 */
function createBoardData(Db $db, string $prefix, array $boards): void
{
    $table = $prefix . BoardService::TABLE_NAME;
    foreach ($boards as $board) {
        $db->insert($table, $board);
    }
}
