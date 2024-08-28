<?php

namespace Core\Database\PDO;

use Core\Database\PDO\Exception\DbConnectException;
use PDO;
use PDOException;

class PostgreSQLConnection implements DbConnect
{
    private string $host = '';
    private string $dbname = '';
    private string $user = '';
    private string $password = '';

    public function __construct(string $host, string $dbname, string $user, string $password)
    {
        $this->host = $host;
        $this->dbname = $dbname;
        $this->user = $user;
        $this->password = $password;
    }

    public function createConnection(): PDO
    {
        try {
            $pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname}",
                $this->user,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => 0,  // PHP 8.4 부터는 bool 타입이나. 암시적 형변환되어 0이면 false로 인식됨.
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );

            return $pdo;
        } catch (PDOException $e) {
            throw new DbConnectException($e->getMessage());
        }
    }
}