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
use DI\Bridge\Slim\Bridge;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\App;
use Slim\Csrf\Guard;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Flash\Messages;
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

// Slim App 생성
$container = new Container();
$app = Bridge::create($container);
$responseFactory = $app->getResponseFactory();

// Container 설정
$container->set(ServerRequestInterface::class, function () {
    $serverRequestCreator = ServerRequestCreatorFactory::create();
    return $serverRequestCreator->createServerRequestFromGlobals();
});

$container->set('csrf', function () use ($responseFactory) {
    $guard = new Guard($responseFactory);
    $guard->setFailureHandler(function (Request $request, RequestHandler $handler) {
        // CSRF 검증 값이 정상적으로 전달되지 않는 경우 중, 
        // Post Data 크기가 post_max_size보다 클 경우, CSRF 검증 실패로 처리되는 문제가 있음.
        $content_length = $request->getServerParams()['CONTENT_LENGTH'] ?? 0;
        $post_max_size = (int)ini_get('post_max_size');
        $post_max_size_byte = $post_max_size * 1024 * 1024; // MB -> Byte
        if ($content_length >= $post_max_size_byte) {
            throw new HttpBadRequestException($request, "Post Data는 최대 {$post_max_size}MB까지 전송 가능합니다.");
        }

        throw new HttpForbiddenException($request, 'CSRF 검증 실패. 새로고침 후 다시 시도하세요.');
    });
    return $guard;
});

$container->set('flash', fn() => new Messages($_SESSION));

// 미들웨어 설정
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();
$app->add(new JsonBodyParserMiddleware());
$app->add(new MethodOverrideMiddleware());
$app->add('csrf');

// Request를 Container에서 가져오기
$request = $container->get(ServerRequestInterface::class);

// 에러 핸들러 설정
$app_debug = $_ENV['APP_DEBUG'] ?? false;
$app_debug = filter_var($app_debug, FILTER_VALIDATE_BOOLEAN);
$callableResolver = $app->getCallableResolver();
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory, $container);
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $app_debug);
register_shutdown_function($shutdownHandler);

$errorMiddleware = $app->addErrorMiddleware($app_debug, true, true);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

// Twig 설정
$twig = setupTwig();
$twig->addExtension(new CsrfExtension($container->get('csrf')));
$twig->addExtension(new FlashExtension($container->get('flash')));
$twig->addExtension(new HtmlExtension());
$twig->addExtension(new IntlExtension());
$app->add(TwigMiddleware::create($app, $twig));

// 기본 경로 설정
$request = $request->withAttribute('base_path', str_replace('\\', '/', __DIR__));
$app->setBasePath(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']) . "/");

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
}

/**
 * 라우트 설정 함수
 */
function setupRoutes(App $app)
{
    $route_files = glob(__DIR__ . "/app/*/Router/*.php");
    foreach ($route_files as $file) {
        include_once $file;
    }
    $route_cache = $_ENV['APP_ROUTE_CACHE'] ?? false;
    if (filter_var($route_cache, FILTER_VALIDATE_BOOLEAN)) {
        $cache_dir = __DIR__ . "/data/cache/API";
        createDirectory($cache_dir);
        $app->getRouteCollector()->setCacheFile("$cache_dir/router-cache.php");
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