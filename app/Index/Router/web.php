<?php

namespace App\Index\Router;

use App\Index\IndexController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

/**
 * @var App $app
 */
$app->group('', function (RouteCollectorProxy $group) {
    $group->get('', [IndexController::class, 'index']);
});
// ->add(ConfigMiddleware::class);
