<?php

namespace Core\Database\PDO;

use PDO;

interface DbConnect
{
    public function __construct(array $params);

    public function createConnection(): PDO;
}