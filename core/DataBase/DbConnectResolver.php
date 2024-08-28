<?php

namespace Core\Database;

use Core\Database\PDO\DbConnect;
use Core\Database\PDO\MySQLConnection;
use Core\Database\PDO\PostgreSQLConnection;

class DbConnectResolver
{
    public static function resolve(array $connect_params = []): DbConnect
    {
        if (empty($connect_params)) {
            $connection = $_ENV['DB_CONNECTION'] ?? '';
            $host = $_ENV['DB_HOST'] ?? '';
            $dbname = $_ENV['DB_DATABASE'] ?? '';
            $user = $_ENV['DB_USERNAME'] ?? '';
            $password = $_ENV['DB_PASSWORD'] ?? '';
        } else {
            $connection = $connect_params['driver'] ?? 'mysql';
            $host = $connect_params['host'] ?? '';
            $dbname = $connect_params['dbname'] ?? '';
            $user = $connect_params['user'] ?? '';
            $password = $connect_params['password'] ?? '';
        }

        switch ($connection) {
            case 'pgsql':
                return new PostgreSQLConnection($host, $dbname, $user, $password);
            case 'mysql':
            default:
                return new MySQLConnection($host, $dbname, $user, $password);
        }
    }
}