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
    $group->get('content/{co_id}', [IndexController::class, 'index'])->setName('content.index');
})
    ->add(TemplateMiddleware::class)
    ->add(ConfigMiddleware::class);