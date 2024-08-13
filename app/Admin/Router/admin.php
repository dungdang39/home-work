<?php

namespace App\Login\Router;

use App\Admin\ConfigController;
use App\Admin\DashboardController;
use App\Admin\LoginController;
use Core\Middleware\AdminMenuAuthMiddleware;
use Core\Middleware\AdminMenuMiddleware;
use Core\Middleware\ConfigMiddleware;
use Core\Middleware\ThemeMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

/**
 * @var App $app
 */
$app->group('admin', function (RouteCollectorProxy $group) {
    // 로그인
    $group->get('/login', [LoginController::class, 'adminLoginPage']);
    $group->post('/login', [LoginController::class, 'Login'])
        ->setName('login');

    $group->group('', function (RouteCollectorProxy $group) {
        $group->get('[/dashboard]', [DashboardController::class, 'index'])
            ->setName('dashboard');
        $group->get('/config', [ConfigController::class, 'index'])
            ->setName('config');
        $group->get('/administrator', [DashboardController::class, 'index'])
            ->setName('administrator');

        // 기본회원 설정
        $group->get('/member/config', [DashboardController::class, 'index'])
            ->setName('member_config');
    })
        ->add(AdminMenuAuthMiddleware::class)
        ->add(AdminMenuMiddleware::class);
})
    ->add(ThemeMiddleware::class)
    ->add(ConfigMiddleware::class);
