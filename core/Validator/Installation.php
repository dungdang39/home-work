<?php

namespace Core\Validator;

use Bootstrap\EnvLoader;
use Install\InstallService;

class Installation
{
    /**
     * 설치 여부를 검사합니다.
     * @param string $path
     * @return void
     */
    public static function validate(string $path): void
    {
        $path = str_replace('\\', '/', $path);
        $env_files = EnvLoader::$option['names'];

        foreach ($env_files as $env) {
            $env_path = $path . '/' . $env;
            if (!file_exists($env_path)) {
                $install_service = new InstallService();
                $template = $install_service->loadTemplate();

                $response_data = [
                    "env_path" => $env_path
                ];
                echo $template->render('error/install_required.html', $response_data);
                exit;
            }
        }
    }
}
