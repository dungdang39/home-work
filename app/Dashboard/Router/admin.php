<?php

namespace App\Login\Router;

use App\Dashboard\DashboardController;
use Core\Middleware\ConfigMiddleware;
use Core\Middleware\ThemeMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

/**
 * @var App $app
 */
$app->group('admin/', function (RouteCollectorProxy $group) {
    $group->get('[dashboard]', [DashboardController::class, 'index'])
        ->setName('dashboard');
})
    ->add(ThemeMiddleware::class)
    ->add(ConfigMiddleware::class);
