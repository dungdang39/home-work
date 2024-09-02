<?php

namespace Core\Database;

use Core\AppConfig;
use PDO;
use Exception;

/**
 * Class Db
 * PDO Wrapper
 */
class Db
{
    private static $instance = null;

    /**
     * @var ?PDO PDO 객체
     */
    private $pdo;

    public function __construct(array $db_settings = []) {
        $this->pdo = DbConnectResolver::resolve($db_settings)->createConnection();
    }

    /**
     * @return Db 인스턴스
     */
    public static function getInstance(): Db
    {
        if (self::$instance === null) {
            self::$instance = new Db();
        }
        return self::$instance;
    }

    public static function setInstance($instance): void
    {
        self::$instance = $instance;
    }

    /**
     * where in 절에 사용할 바인딩 자리 생성.
     * @param array $values
     * @return string
     */
    public static function makeWhereInPlaceHolder(array $values)
    {
        return str_repeat('?, ', count($values) - 1) . '?';
    }


    /**
     * PDO 객체를 반환함.
     * @return PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }


    public function __destruct()
    {
        $this->pdo = null;
    }

    /**
     * 쿼리를 실행하고 PDO 객체를 반환함.
     * @param string $query
     * @param array $params
     * @return \PDOStatement
     */
    public function run($query, $params = [])
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        if (AppConfig::getInstance()->get('DEBUG')) {
            $this->logging_last_stmt($stmt);
        }
        return $stmt;
    }

    /**
     * insert 쿼리
     * @param $table
     * @param array $data associative array
     * @return false|string
     */
    public function insert($table, array $data)
    {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        $this->run("INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})", array_values($data));

        return $this->pdo->lastInsertId();
    }

    /**
     * 업데이트 쿼리
     * @param string $table
     * @param array $updateData [column => value]
     * @param array $where [column => value]
     * @return int
     */
    public function update($table, $updateData, $where = [])
    {
        $values = [];

        $fields = null;
        foreach ($updateData as $key => $value) {
            $key = '`' . trim($key, '`') . '`';
            $fields .= "$key = ?,";
            $values[] = $value;
        }
        $fields = rtrim($fields, ',');

        $whereCondition = null;
        $i = 0;
        foreach ($where as $key => $value) {
            $key = '`' . trim($key, '`') . '`';
            $whereCondition .= $i == 0 ? "$key = ?" : " AND $key = ?";
            $values[] = $value;
            $i++;
        }
        $whereCondition = $whereCondition ? "WHERE {$whereCondition}" : '';

        $query = "UPDATE `$table` SET $fields {$whereCondition}";
        return $this->run($query, $values)->rowCount();
    }

    /**
     * 삭제 쿼리
     * @param string $table
     * @param array $where [column => value]
     * @param ?int $limit
     * @return int
     */
    public function delete($table, $where, $limit = null)
    {
        $values = array_values($where);

        $whereCondition = null;
        $i = 0;
        foreach ($where as $key => $value) {
            $key = '`' . trim($key, '`') . '`';
            $whereCondition .= $i == 0 ? "$key = ?" : " AND $key = ?";
            $i++;
        }

        // limit 제한시
        if (is_numeric($limit)) {
            $limit = "LIMIT $limit";
        }

        $stmt = $this->run("DELETE FROM `{$table}` WHERE {$whereCondition} {$limit}", $values);
        return $stmt->rowCount();
    }

    /**
     * @param string $table
     * @param string $id_column
     * @param string $id_value
     * @return int
     */
    public function deleteById($table, $id_column, $id_value)
    {
        $stmt = $this->run("DELETE FROM {$table} WHERE {$id_column} = ?", [$id_value]);
        return $stmt->rowCount();
    }

    /**
     * id 컬럼 기준으로 여러 행을 지운다.
     * @param string $table
     * @param string $id_column
     * @param string $id_values
     * @return int 지워진 행 수
     */
    public function deleteByIds($table, $id_column, $id_values)
    {
        $stmt = $this->run("DELETE FROM {$table} WHERE {$id_column} IN ({$id_values})");
        return $stmt->rowCount();
    }

    /**
     * 테이블의 모든 데이터를 지운다.
     * @param string $table
     * @return int
     */
    public function deleteAll($table)
    {
        $stmt = $this->run("DELETE FROM {$table}");
        return $stmt->rowCount();
    }

    /**
     * 마지막 실행된 쿼리를 로그파일에 기록.
     * @param $stmt
     * @return void
     */
    public function logging_last_stmt($stmt)
    {
        error_log($stmt->queryString);
        ob_start();
        $stmt->debugDumpParams();
        $paramInfo = ob_get_clean();
        //@todo app에서 관리하는 로깅으로 변경필요.
        error_log("Parameter info: \n" . $paramInfo);
    }

    /**
     * 테이블이 존재하는지 확인.
     * @param string $table
     * @return bool
     */
    public function isTableExists(string $table): bool
    {
        $query = "SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = :table AND TABLE_SCHEMA = DATABASE()";
        $params = ['table' => $table];

        $stmt = $this->run($query, $params);
        return $stmt->rowCount() > 0;
    }

    /**
     * 데이터베이스 연결을 테스트.
     * @param array $db_settings
     * @return array
     */
    public static function testConnection(array $db_settings): array
    {
        try {
            $pdo = DbConnectResolver::resolve($db_settings)->createConnection();
            $pdo->query('SELECT 1');

            return ['success' => true, 'message' => 'Database connection is successful.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}