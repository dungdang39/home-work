<?php

use Core\Handlers\HttpErrorHandler;
use Core\Handlers\ShutdownHandler;
use API\Middleware\JsonBodyParserMiddleware;
use API\ResponseEmitter\ResponseEmitter;
use App\Admin\Service\ThemeService;
use App\Config\ConfigService;
use Core\Environment;
use Core\Extension\CsrfExtension;
use Core\Extension\FlashExtension;
use Core\Middleware\FlashDataMiddleware;
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

require __DIR__ . '/vendor/autoload.php';

/**
 * @todo: 수정 필요
 */ 
define('_GNUBOARD_', true);
include_once(__DIR__.'/lib/hook.lib.php');  // hook 함수 파일
include_once(__DIR__.'/lib/common.lib.php');    // 공통 라이브러리

//-------------------------

// Set error display settings
// - Should be set to false in production
$displayErrorDetails = true;

// Start PHP session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!file_exists(__DIR__ . '/.env')) {
    header('Location: install/index.php');
    exit;
}

/**
 * Instantiate App
 */
// Create Container
$container = new Container();
AppFactory::setContainer($container);

// Create App
$app = AppFactory::create();
$callableResolver = $app->getCallableResolver();
$responseFactory = $app->getResponseFactory();

// Register Middleware On Container
$container->set('csrf', function () use ($responseFactory) {
    $guard = new Guard($responseFactory);
    // CSRF 검증 실패시 처리할 핸들러 설정
    $guard->setFailureHandler(function (Request $request, RequestHandler $handler) {
        $message = 'CSRF 검증이 실패했습니다. 새로고침 후 다시 시도해주세요.';
        throw new HttpForbiddenException($request, $message);
    });
    return $guard;
});

$container->set('flash', function () {
    return new Messages($_SESSION);
});

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

/**
 * Changing the default invocation strategy on the RouteCollector component
 * will change it for every route being defined after this change being applied
 */
$routeCollector = $app->getRouteCollector();
$routeCollector->setDefaultInvocationStrategy(new RequestResponseArgs());

// Twig & 테마경로 설정
try {
    // .env 파일에서 환경 변수 로드
    Environment::load(__DIR__, ["only_env" => true]);

    $theme_dir = __DIR__ . '/' . ThemeService::DIRECTORY;
    ThemeService::setBaseDir($theme_dir);

    $config_service = new ConfigService();
    $theme_service = new ThemeService();
    $theme = $config_service->getTheme();
    if (!$theme_service->existsTheme($theme)) {
        $theme = ThemeService::DEFAULT_THEME;
    }

    $template_dir = $theme_dir . '/' . $theme;
    $template_dir = str_replace('\\', '/', $template_dir);

} catch (InvalidPathException $e) {
    
}

$twig = Twig::create($template_dir, ['cache' => false]);
$twig->addExtension(new FlashExtension($container->get('flash')));
$twig->addExtension(new HtmlExtension());
$twig->addExtension(new CsrfExtension($container->get('csrf')));

/**
 * Add Middleware
 */
// The routing middleware should be added earlier than the ErrorMiddleware
// Otherwise exceptions thrown from it will not be handled by the middleware
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();

// Add CSRF Protection Middleware
$app->add('csrf');

// Add Flash Data Middleware
$app->add(new FlashDataMiddleware($container->get('flash')));

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

// Add JSON Body Parser Middleware
$app->add(new JsonBodyParserMiddleware());

// Add MethodOverride middleware
// X-Http-Method-Override 헤더 또는 _METHOD 폼 파라미터를 사용하여 HTTP 메소드를 재정의합니다.
$app->add(new MethodOverrideMiddleware());

// Create Error Handler
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);

// Create Shutdown Handler
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
register_shutdown_function($shutdownHandler);

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, true, true);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

/**
 * Add Routers
 */
// Set the base path for the API version
$path = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
$app->setBasePath("{$path}/");

// Include all Routers in the app
$routerFiles = glob(__DIR__ . "/app/*/Router/*.php");
foreach ($routerFiles as $routerFile) {
    include_once $routerFile;
}

/**
 * Route Cache (Optional)
 * To generate the route cache data, you need to set the file to one that does not exist in a writable directory.
 * After the file is generated on first run, only read permissions for the file are required.
 *
 * You may need to generate this file in a development environment and committing it to your project before deploying
 * if you don't have write permissions for the directory where the cache file resides on the server it is being deployed to
 */
/*
$cache_dir = G5_DATA_PATH . "/cache/API";
if (!is_dir($cache_dir)) {
    @mkdir($cache_dir, G5_DIR_PERMISSION);
}
$routeCollector = $app->getRouteCollector();
$routeCollector->setCacheFile("{$cache_dir}/router-cache.php");
*/

// Run App & Custom Emit Response
$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);