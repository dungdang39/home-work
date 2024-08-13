<?php

namespace App\Login\Router;

use App\Admin\DashboardController;
use App\Admin\LoginController;
use Core\Middleware\ConfigMiddleware;
use Core\Middleware\ThemeMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

/**
 * @var App $app
 */
$app->group('admin/', function (RouteCollectorProxy $group) {
    // 로그인
    $group->get('login', [LoginController::class, 'adminLoginPage']);
    $group->post('login', [LoginController::class, 'Login'])
        ->setName('login');

    $group->group('', function (RouteCollectorProxy $group) {
        // 대시보드
        $group->get('[dashboard]', [DashboardController::class, 'index'])
            ->setName('dashboard');
    });
        // ->add()  // 로그인&권한 체크
        // ->add(); // 관리자메뉴 및 권한 체크
})
    ->add(ThemeMiddleware::class)
    ->add(ConfigMiddleware::class);
