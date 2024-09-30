<?php

namespace Bootstrap;

use Slim\App;
use Core\FileService;

class RouterConfig
{
    private const ROUTER_DIRECTORY = '/app/*/Router/*.php';
    private const CACHE_DIRECTORY = '/data/cache/API';

    /**
     * 라우트 설정을 구성합니다.
     * 
     * @param App $app
     * @return void
     */
    public static function configure(App $app): void
    {
        self::includeRouteFiles($app);
        self::setupRouteCache($app);
    }

    /**
     * 라우트 파일을 포함합니다.
     * 
     * @return void
     */
    private static function includeRouteFiles(App $app): void
    {
        $route_files = glob(dirname(__DIR__) . self::ROUTER_DIRECTORY);
        foreach ($route_files as $file) {
            $include = include_once $file;
            if (is_callable($include)) {
                $include($app);
            }
        }
    }

    /**
     * 라우트 캐시를 설정합니다.
     * 
     * @param App $app
     * @return void
     */
    private static function setupRouteCache(App $app): void
    {
        $cache_directory = dirname(__DIR__) . self::CACHE_DIRECTORY;
        $route_cache = $_ENV['APP_ROUTE_CACHE'] ?? false;

        if (filter_var($route_cache, FILTER_VALIDATE_BOOLEAN)) {
            FileService::createDirectory($cache_directory);
            $app->getRouteCollector()->setCacheFile($cache_directory . '/router-cache.php');
        }
    }
}
