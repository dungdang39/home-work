<?php

namespace Core;

use Dotenv\Dotenv;
use Dotenv\Repository\Adapter\EnvConstAdapter;
use Dotenv\Repository\RepositoryBuilder;

class Environment
{
    public static array $option = [
        "names" => ['.env'],
        "only_env" => false
    ];

    /**
     * .env 파일 로드
     * - $_ENV, $_SERVER 전역변수 사용
     * @param string $path
     * @param array $option  only_env: $_ENV 전역변수만 사용하도록 설정
     * @return void
     */
    public static function load(string $path, array $option = []): void
    {
        $option = array_merge(self::$option, $option);

        if ($option['only_env']) {
            $dotenv = self::createOnlyEnvConstAdapter($path);
        } else {
            $dotenv = self::create($path);
        }

        $dotenv->load();
    }

    /**
     * Dotenv 객체 생성 
     * @param string $path
     * @param string|null $name
     * @return Dotenv
     */
    private static function create(string $path): Dotenv
    {
        return Dotenv::createImmutable($path, self::$option['names']);
    }

    /**
     * EnvConstAdapter만 사용하는 Dotenv 객체 생성
     * @param string $path
     * @param string|null $name
     * @return Dotenv
     */
    private static function createOnlyEnvConstAdapter(string $path): Dotenv
    {
        $repository_builder = RepositoryBuilder::createWithNoAdapters();
        $repository_builder = $repository_builder->addAdapter(EnvConstAdapter::class);
        $repository = $repository_builder->immutable()->make();

        return Dotenv::create($repository, $path, self::$option['names']);
    }
}
