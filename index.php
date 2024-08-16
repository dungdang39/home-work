<?php

use API\Handlers\HttpErrorHandler;
use API\Handlers\ShutdownHandler;
use API\Middleware\JsonBodyParserMiddleware;
use API\ResponseEmitter\ResponseEmitter;
use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/vendor/autoload.php';

//gnuboard 로딩
$g5_path = g5_root_path();
require_once(__DIR__ . '/config.php');   // 설정 파일

include_once(G5_LIB_PATH.'/hook.lib.php');    // hook 함수 파일
include_once (G5_LIB_PATH.'/common.lib.php'); // 공통 라이브러리 // @todo 정리후 삭제대상

//-------------------------

// Set error display settings
// - Should be set to false in production
$displayErrorDetails = true;

/**
 * Instantiate App
 */
$container = new Container();
AppFactory::setContainer($container);
$app = AppFactory::create();

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

// Create Twig
$twig = Twig::create(__DIR__, ['cache' => false]);

/**
 * Add Middleware
 */
// The routing middleware should be added earlier than the ErrorMiddleware
// Otherwise exceptions thrown from it will not be handled by the middleware
$app->addRoutingMiddleware();

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

// Add JSON Body Parser Middleware
$app->add(new JsonBodyParserMiddleware());

// Create Error Handler
$callableResolver = $app->getCallableResolver();
$responseFactory = $app->getResponseFactory();
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);

// Create Shutdown Handler
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
register_shutdown_function($shutdownHandler);

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, true, true);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

// ssession
session_start();

/**
 * Add Routers
 */
// Set the base path for the API version
$path = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
$app->setBasePath("{$path}/");

// Include all Routers for the requested API version.
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