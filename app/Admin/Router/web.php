<?php

namespace App\Login\Router;

use App\Index\IndexController;
use Core\Middleware\ConfigMiddleware;
use Core\Middleware\TemplateMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

/**
 * @var App $app
 */
$app->group('', function (RouteCollectorProxy $group) {
    $group->get('content/{code}', [IndexController::class, 'index'])->setName('content');
})
    ->add(TemplateMiddleware::class)
    ->add(ConfigMiddleware::class);