<?php

namespace Bootstrap;

use Dotenv\Dotenv;
use Dotenv\Repository\Adapter\EnvConstAdapter;
use Dotenv\Repository\RepositoryBuilder;

class EnvLoader
{
    public const ENV_FILE = '.env';

    public static array $option = [
        "names" => [self::ENV_FILE],
        "only_env" => true
    ];

    private $dotenv;

    private static ?self $instance = null;

    private function __construct(array $option = [])
    {
        $path = dirname(__DIR__);
        $option = array_merge(self::$option, $option);

        if ($option['only_env']) {
            $this->dotenv = self::createOnlyEnvConstAdapter($path);
        } else {
            $this->dotenv = self::create($path);
        }
    }

    /**
     * .env 파일 로드
     * - $_ENV, $_SERVER 전역변수 사용
     * @param array $option  only_env: $_ENV 전역변수만 사용하도록 설정
     * @return void
     */
    public static function load(): void
    {
        $instance = self::getInstance();
        $instance->dotenv->load();
    }

    public static function getInstance(array $option = []): self
    {
        if (self::$instance === null) {
            self::$instance = new self($option);
        }

        return self::$instance;
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
