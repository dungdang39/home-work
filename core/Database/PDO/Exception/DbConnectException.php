<?php

namespace Core\Database\PDO\Exception;

use RuntimeException;

class DbConnectException extends RuntimeException
{
    /**
     * @var int
     */
    protected $code = 500;

    /**
     * @var string
     */
    protected $message = 'Database connection failed.';

    protected string $title = 'Internal Server Error';
    protected string $description = 'database connection failed';
}
