<?php

namespace App\Login\Router;

use App\Admin\Controller\AdministratorController;
use App\Admin\Controller\ConfigController;
use App\Admin\Controller\LoginController;
use App\Admin\Controller\DashboardController;
use App\Admin\Controller\MenuController;
use App\Admin\Controller\ThemeController;
use Core\Middleware\AdminMenuAuthMiddleware;
use Core\Middleware\AdminMenuMiddleware;
use Core\Middleware\ConfigMiddleware;
use Core\Middleware\LoginAuthMiddleware;
use Core\Middleware\SuperAdminAuthMiddleware;
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

    // 관리자
    $group->group('', function (RouteCollectorProxy $group) {
        // 대시보드
        $group->get('[/dashboard]', [DashboardController::class, 'index'])->setName('dashboard');

        // 기본환경 설정
        $group->group('/config', function (RouteCollectorProxy $group) {
            $group->get('', [ConfigController::class, 'index'])->setName('config.index');
            $group->post('', [ConfigController::class, 'update'])->setName('config.update');
        })->add(SuperAdminAuthMiddleware::class);

        // 운영진 설정
        $group->group('/administrator', function (RouteCollectorProxy $group) {
            $group->get('', [AdministratorController::class, 'index'])->setName('administrator.index');
            $group->post('', [AdministratorController::class, 'insert'])->setName('administrator.insert');
            $group->put('/{mb_id}', [AdministratorController::class, 'update'])->setName('administrator.update');
            $group->delete('', [AdministratorController::class, 'delete'])->setName('administrator.delete');
        })->add(SuperAdminAuthMiddleware::class);

        // 메뉴 설정
        $group->group('/menu', function (RouteCollectorProxy $group) {
            $group->get('', [MenuController::class, 'index'])->setName('menu.index');
            $group->get('/{type}', [MenuController::class, 'getUrls'])->setName('menu.urls');
            $group->post('', [MenuController::class, 'insert'])->setName('menu.insert');
            $group->put('/{me_id}', [MenuController::class, 'update'])->setName('menu.update');
            $group->delete('', [MenuController::class, 'delete'])->setName('menu.delete');
        })->add(AdminMenuAuthMiddleware::class);

        // 디자인/UI
        $group->group('/design', function (RouteCollectorProxy $group) {
            // 테마
            $group->group('/theme', function (RouteCollectorProxy $group) {
                $group->get('', [ThemeController::class, 'index'])->setName('theme.index');
                $group->get('/{theme}', [ThemeController::class, 'view'])->setName('theme.view');
                $group->put('/{theme}', [ThemeController::class, 'update'])->setName('theme.update');
            });

            // 배너
            $group->group('/banner', function (RouteCollectorProxy $group) {
                $group->get('', [DashboardController::class, 'index'])->setName('banner.index');
                $group->get('/create', [DashboardController::class, 'create'])->setName('banner.create');
                $group->post('', [DashboardController::class, 'insert'])->setName('banner.insert');
                $group->get('/{bn_id}', [DashboardController::class, 'view'])->setName('banner.view');
                $group->post('/{bn_id}', [DashboardController::class, 'update'])->setName('banner.update');
                $group->delete('/{bn_id}', [DashboardController::class, 'delete'])->setName('banner.delete');
            });

            // 레이어팝업
            $group->group('/popup', function (RouteCollectorProxy $group) {
                $group->get('', [DashboardController::class, 'index'])->setName('popup.index');
                $group->get('/create', [DashboardController::class, 'create'])->setName('popup.create');
                $group->post('', [DashboardController::class, 'insert'])->setName('popup.insert');
                $group->get('/{nw_id}', [DashboardController::class, 'view'])->setName('popup.view');
                $group->post('/{nw_id}', [DashboardController::class, 'update'])->setName('popup.update');
                $group->delete('/{nw_id}', [DashboardController::class, 'delete'])->setName('popup.delete');
            });
        })->add(AdminMenuAuthMiddleware::class);

        // 기본회원 설정
        $group->group('/member', function (RouteCollectorProxy $group) {
            $group->get('/config', [DashboardController::class, 'index'])->setName('member.config.index');
        })->add(AdminMenuAuthMiddleware::class);
    })
        ->add(AdminMenuMiddleware::class)
        ->add(LoginAuthMiddleware::class);
})
    ->add(TemplateMiddleware::class)
    ->add(ConfigMiddleware::class);
