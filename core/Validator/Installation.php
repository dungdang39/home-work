<?php

namespace Core\Validator;

use Core\Environment;

class Installation
{
    /**
     * 설치 여부를 확인합니다.
     * 
     * @param string $path
     * @return void
     */
    public static function validate(string $path): void
    {
        $env_files = Environment::$option['names'];

        foreach ($env_files as $env) {
            $env_path = $path . '/' . $env;
            if (!file_exists($env_path)) {
                header('Location: install/index.php');
                exit;
            }
        }
    }
}
