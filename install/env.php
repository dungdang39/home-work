<?php

use Bootstrap\EnvLoader;
use Core\Database\Db;
use Install\InstallService;

require dirname(__DIR__) . '/vendor/autoload.php';

function getUserInput($prompt = "") {
    if (!empty($prompt)) {
        echo "$prompt: ";
    }
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);
    return $line;
}

function confirmAction($message) {
    $input = getUserInput($message);
    if ($input !== 'y' && $input !== '') {
        echo "설치가 취소되었습니다.\n";
        exit(0);
    }
}

if (file_exists(dirname(__DIR__) . '/' . EnvLoader::ENV_FILE)) {
    confirmAction('.env 파일이 이미 존재합니다. 덮어쓰시겠습니까? (y/n)');
} else {
    confirmAction('그누보드 .env 파일을 생성하시겠습니까? (y/n)');
}

/**
 * 1. .env 파일 정보 입력
 */
$app_url = '';
$database_setting = [];
$envSettings = [
    'APP_URL'       => ['default' => '', 'description' => 'Application URL'],
    'DB_HOST'       => ['db_key' => 'host', 'default' => '127.0.0.1', 'description' => 'Database Host (default: 127.0.0.1)'],
    // 추후 다른 DB 연결 방식을 지원할 수 있도록 수정 필요
    // 'DB_CONNECTION' => ['db_key' => 'connection', 'default' => 'mysql', 'description' => 'Database Connection (default: mysql)'],
    'DB_PORT'       => ['db_key' => 'port', 'default' => '3306', 'description' => 'Database Port (default: 3306)'],
    'DB_DBNAME'     => ['db_key' => 'dbname', 'default' => '', 'description' => 'Database Name (Required)'],
    'DB_USERNAME'   => ['db_key' => 'user', 'default' => '', 'description' => 'Database Username (Required)'],
    'DB_PASSWORD'   => ['db_key' => 'password', 'default' => '', 'description' => 'Database Password (Required)'],
    'DB_PREFIX'     => ['db_key' => 'prefix', 'default' => 'new_', 'description' => 'Database Prefix (default: new_)']
];

foreach ($envSettings as $key => $settings) {
    $value = getUserInput($settings['description']) ?: ($settings['default'] ?? '');
    if (empty($value) && $settings['default'] === '' && $key !== 'APP_URL') {
        echo "{$settings['description']}은(는) 필수 항목입니다.\n";
        exit(0);
    }

    if ($key === 'APP_URL') {
        $app_url = rtrim($value, '/');
    }
    if (isset($settings['db_key'])) {
        $database_setting[$settings['db_key']] = $value;
    }
}

/**
 * 2. 데이터베이스 연결 테스트
 */
$result = Db::testConnection($database_setting);
if (!$result['success']) {
    echo "MySQL 연결에 실패하였습니다. {$result['message']}\n";
    exit(0);
}

/**
 * 3. .env 파일 생성
 */
$install = new InstallService();
$install->createEnvFile([
    'app_url' => $app_url,
    'db_host' => $database_setting['host'],
    'db_dbname' => $database_setting['dbname'],
    'db_user' => $database_setting['user'],
    'db_password' => $database_setting['password'],
    'db_prefix' => $database_setting['prefix']
]);
echo ".env 파일이 생성되었습니다.\n그누보드 설치 페이지에 접속하여 설치를 계속 진행해주세요.\n";