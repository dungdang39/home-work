<?php

namespace Bootstrap;

use App\Base\Service\ConfigService;
use Slim\App;
use Core\FileService;

class RouterConfig
{
    private const ROUTER_DIRECTORY = '/app/*/Router/*.php';
    private const PLUGIN_ROUTER_DIRECTORY = '/plugin/{plugin}/router/*.php';
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
        self::includePluginRouteFiles($app);
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
     * 플러그인 라우트 파일을 포함합니다.
     * 
     * @return void
     */
    private static function includePluginRouteFiles(App $app): void
    {
        $config_service = $app->getContainer()->get(ConfigService::class);
        $active_plugins = $config_service->getActivePlugins();

        foreach ($active_plugins as $plugin) {
            $plugin_dir = str_replace('{plugin}', $plugin, self::PLUGIN_ROUTER_DIRECTORY);
            $route_files = glob(dirname(__DIR__) . $plugin_dir);
            foreach ($route_files as $file) {
                include_once $file;
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
