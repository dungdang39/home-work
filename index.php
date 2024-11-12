<?php

/**
 * Slim Framework를 사용한 애플리케이션 진입점
 */

use Bootstrap\TwigConfig;
use Bootstrap\ContainerConfig;
use Bootstrap\EnvLoader;
use Bootstrap\RouterConfig;
use Core\Handlers\HttpErrorHandler;
use Core\Handlers\ShutdownHandler;
use Core\Middleware\JsonBodyParserMiddleware;
use Core\PluginService;
use DI\Container;
use DI\Bridge\Slim\Bridge;
use Install\InstallValidateService;
use Slim\Http\ServerRequest as Request;
use Slim\Middleware\ContentLengthMiddleware;
use Slim\Middleware\MethodOverrideMiddleware;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/vendor/autoload.php';

define('_GNUBOARD_', true);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// 환경설정 로드
EnvLoader::load();

// Slim App 생성
$container = new Container();
$app = Bridge::create($container);
$responseFactory = $app->getResponseFactory();
$container->set('responseFactory', $responseFactory);

// Container 설정
ContainerConfig::configure($container);

// 미들웨어 설정
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();
$app->add(new JsonBodyParserMiddleware());
$app->add(new MethodOverrideMiddleware());
$app->add(new ContentLengthMiddleware());
$app->add('csrf');

// Request를 Container에서 가져오기
$request = $container->get(Request::class);

// 에러 핸들러 설정
$app_debug = $_ENV['APP_DEBUG'] ?? false;
$app_debug = filter_var($app_debug, FILTER_VALIDATE_BOOLEAN);
$callableResolver = $app->getCallableResolver();
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory, $container);
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $app_debug);
register_shutdown_function($shutdownHandler);

$errorMiddleware = $app->addErrorMiddleware($app_debug, true, true);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

// 설치 여부 확인
InstallValidateService::checkInstall();

// Twig 설정
$twig = TwigConfig::configure($container);
$app->add(TwigMiddleware::create($app, $twig));

// 기본 경로 설정
$request = $request->withAttribute('base_path', str_replace('\\', '/', __DIR__));
$app->setBasePath(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']) . "/");

// 라우트 설정
RouterConfig::configure($app);

// 플러그인 설정파일 로드
PluginService::runActivePlugins($app);

// 앱 실행
$app->run($request);