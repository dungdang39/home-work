<?php

namespace Install;

use Core\Database\Db;

/**
 * 설치 서비스 클래스
 */
class InstallService
{
    private $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    /**
     * 테이블 생성
     * @param string $file_path 파일 경로
     */
    public function createTable(string $prefix, string $file_path): void
    {
        $file = implode('', file($file_path));
        eval("\$file = \"$file\";");

        $file = preg_replace('/^--.*$/m', '', $file);
        $file = preg_replace('/`new_([^`]+`)/', '`' . $prefix . '$1', $file);
        $f = explode(';', $file);
        for ($i = 0; $i < count($f); $i++) {
            if (trim($f[$i]) == '') {
                continue;
            }

            $sql = get_db_create_replace($f[$i]);
            $this->db->run($sql);
        }
    }
}
