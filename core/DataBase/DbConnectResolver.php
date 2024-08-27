<?php

namespace Core\Database;

use Core\Database\PDO\DbConnect;
use Core\Database\PDO\MySQLConnection;
use Core\Database\PDO\PostgreSQLConnection;

class DbConnectResolver
{
    public static function resolve(array $connect_params = []): DbConnect
    {
        if (empty($params)) {
            $connection = $_ENV['DB_CONNECTION'];
            $connect_params = [
                'host' => $_ENV['DB_HOST'] ?? '',
                'dbname' => $_ENV['DB_DATABASE'] ?? '',
                'user' => $_ENV['DB_USERNAME'] ?? '',
                'password' => $_ENV['DB_PASSWORD'] ?? '',
            ];
        } else {
            $connection = $connect_params['driver'] ?? 'mysql';
        }

        switch ($connection) {
            case 'pgsql':
                return new PostgreSQLConnection($connect_params);
            case 'mysql':
            default:
                return new MySQLConnection($connect_params);
        }
    }
}