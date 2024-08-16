<?php

namespace App\Login\Router;

use App\Admin\Controller\ConfigController;
use App\Admin\DashboardController;
use App\Admin\LoginController;
use Core\Middleware\AdminMenuAuthMiddleware;
use Core\Middleware\AdminMenuMiddleware;
use Core\Middleware\ConfigMiddleware;
use Core\Middleware\LoginAuthMiddleware;
use Core\Middleware\TemplateMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

/**
 * @var App $app
 */
$app->group('admin', function (RouteCollectorProxy $group) {
    // 로그인
    $group->get('/login', [LoginController::class, 'adminLoginPage'])->setName('login.index');
    $group->post('/login', [LoginController::class, 'Login'])->setName('login');
    // 로그아웃
    $group->get('/logout', [LoginController::class, 'Logout'])->setName('logout');
    // 대시보드
    $group->get('[/dashboard]', [DashboardController::class, 'index'])
        ->setName('dashboard')
        ->add(AdminMenuMiddleware::class)
        ->add(LoginAuthMiddleware::class);
    $group->group('', function (RouteCollectorProxy $group) {
        // 기본환경 설정
        $group->group('/config', function (RouteCollectorProxy $group) {
            $group->get('', [ConfigController::class, 'index'])->setName('config.index');
            $group->post('', [ConfigController::class, 'update'])->setName('config.update');
        });
        // 운영진 설정
        $group->group('/administrator', function (RouteCollectorProxy $group) {
            $group->get('', [DashboardController::class, 'index'])->setName('administrator.index');
            $group->post('', [DashboardController::class, 'update'])->setName('administrator.update');
            $group->delete('', [DashboardController::class, 'delete'])->setName('administrator.delete');
        });
        // 기본회원 설정
        $group->get('/member/config', [DashboardController::class, 'index'])->setName('member_config');
    })
        ->add(AdminMenuAuthMiddleware::class)
        ->add(AdminMenuMiddleware::class)
        ->add(LoginAuthMiddleware::class);
})
    ->add(TemplateMiddleware::class)
    ->add(ConfigMiddleware::class);
