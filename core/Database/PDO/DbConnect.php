<?php

namespace Core\Database\PDO;

use PDO;

interface DbConnect
{
    public function __construct(string $host, string $dbname, string $user, string $password);

    public function createConnection(): PDO;
}