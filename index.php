<?php

use API\Middleware\JsonBodyParserMiddleware;
use API\ResponseEmitter\ResponseEmitter;
use App\Admin\Service\ThemeService;
use App\Config\ConfigService;
use Core\Environment;
use Core\Extension\CsrfExtension;
use Core\Extension\FlashExtension;
use Core\Handlers\HttpErrorHandler;
use Core\Handlers\ShutdownHandler;
use DI\Container;
use Dotenv\Exception\InvalidPathException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Csrf\Guard;
use Slim\Exception\HttpForbiddenException;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Flash\Messages;
use Slim\Handlers\Strategies\RequestResponseArgs;
use Slim\Middleware\MethodOverrideMiddleware;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Twig\Extra\Html\HtmlExtension;
use Twig\Extra\Intl\IntlExtension;

require __DIR__ . '/vendor/autoload.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

validateIsInstalled(__DIR__);

Environment::load(__DIR__, ["only_env" => true]);

/**
 * @todo: 추후 common.lib.php 코드 내부 내용을 개선하여
 * composer autoload로 로드하도록 수정할 예정이므로 제거할 것
 */
define('_GNUBOARD_', true);
include_once(__DIR__ . '/lib/common.lib.php');

// Slim & Container 생성
$container = new Container();
AppFactory::setContainer($container);
$app = AppFactory::create();
$responseFactory = $app->getResponseFactory();

// CSRF 및 Flash 컨테이너 설정
$container->set('csrf', function () use ($responseFactory) {
    $guard = new Guard($responseFactory);
    $guard->setFailureHandler(function (Request $request, RequestHandler $handler) {
        throw new HttpForbiddenException($request, 'CSRF 검증 실패. 새로고침 후 다시 시도하세요.');
    });
    return $guard;
});

$container->set('flash', fn() => new Messages($_SESSION));

// Twig & Extension 설정
$twig = setupTwig();
$twig->addExtension(new CsrfExtension($container->get('csrf')));
$twig->addExtension(new FlashExtension($container->get('flash')));
$twig->addExtension(new HtmlExtension());
$twig->addExtension(new IntlExtension());

// 미들웨어 설정
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();
$app->add(new JsonBodyParserMiddleware());
$app->add(new MethodOverrideMiddleware());
$app->add(TwigMiddleware::create($app, $twig));
$app->add('csrf');

// 전역에서 Request 객체 생성
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

// 에러 핸들러 설정
$app_debug = $_ENV['APP_DEBUG'] ?? false;
$callableResolver = $app->getCallableResolver();
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $app_debug);
register_shutdown_function($shutdownHandler);

$errorMiddleware = $app->addErrorMiddleware($app_debug, true, true);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

// 라우트 설정
setupRoutes($app);

// 앱 실행 및 커스텀 응답
$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);


/**
 * 설치 여부 확인
 * - .env 설정 파일들이 존재하지 않으면 설치 페이지로 이동
 */
function validateIsInstalled($path)
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

/**
 * Twig 및 테마 설정 함수
 */
function setupTwig()
{
    try {
        $theme_dir = __DIR__ . '/' . ThemeService::DIRECTORY;
        ThemeService::setBaseDir($theme_dir);

        $config_service = new ConfigService();
        $theme_service = new ThemeService();
        $theme = $config_service->getTheme();

        if (!$theme_service->existsTheme($theme)) {
            $theme = ThemeService::DEFAULT_THEME;
        }

        $template_dir = str_replace('\\', '/', "$theme_dir/$theme");

        $cache_dir = __DIR__ . "/data/cache/twig";
        createDirectory($cache_dir);

        return Twig::create($template_dir, ['cache' => $cache_dir, 'auto_reload' => true]);
    } catch (InvalidPathException $e) {
        // Handle the exception as necessary
    }
}

/**
 * 라우트 설정 함수
 */
function setupRoutes($app)
{
    $app->setBasePath(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']) . "/");

    $route_collector = $app->getRouteCollector();
    $route_collector->setDefaultInvocationStrategy(new RequestResponseArgs());

    $route_files = glob(__DIR__ . "/app/*/Router/*.php");
    foreach ($route_files as $file) {
        include_once $file;
    }

    if ($_ENV['APP_ROUTE_CACHE'] ?? false) {
        $cache_dir = __DIR__ . "/data/cache/API";
        createDirectory($cache_dir);
        $route_collector->setCacheFile("$cache_dir/router-cache.php");
    }
}

/**
 * 디렉토리 생성 함수
 */
function createDirectory($dir, $permissions = 0755)
{
    if (!is_dir($dir)) {
        @mkdir($dir, $permissions);
    }
}