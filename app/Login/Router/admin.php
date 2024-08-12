<?php

namespace App\Login\Router;

use App\Login\LoginController;
use Core\Middleware\ConfigMiddleware;
use Core\Middleware\ThemeMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

/**
 * @var App $app
 */
$app->group('admin/', function (RouteCollectorProxy $group) {
    $group->get('login', [LoginController::class, 'adminLoginPage']);
    $group->post('login', [LoginController::class, 'Login'])
        ->setName('login');
})
    ->add(ThemeMiddleware::class)
    ->add(ConfigMiddleware::class);
