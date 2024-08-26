<?php

namespace App\Login\Router;

use App\Admin\Controller\AdministratorController;
use App\Admin\Controller\ConfigController;
use App\Admin\Controller\LoginController;
use App\Admin\Controller\DashboardController;
use App\Admin\Controller\MenuController;
use App\Admin\Controller\ThemeController;
use App\Banner\Controller\BannerController;
use App\Member\Controller\MemberConfigController;
use App\Member\Controller\MemberController;
use App\Popup\Controller\PopupController;
use Core\Middleware\AdminMenuAuthMiddleware;
use Core\Middleware\AdminMenuMiddleware;
use Core\Middleware\ConfigMiddleware;
use Core\Middleware\LoginAuthMiddleware;
use Core\Middleware\SuperAdminAuthMiddleware;
use Core\Middleware\TemplateMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

/**
 * 관리자 페이지 라우터를 정의합니다.
 * - Slim Framework에서 파일전송은 post 방식으로만 가능합니다.
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
                $group->post('/{theme}', [ThemeController::class, 'update'])->setName('theme.update');
            })->add(SuperAdminAuthMiddleware::class);

            // 배너
            $group->group('/banner', function (RouteCollectorProxy $group) {
                $group->get('', [BannerController::class, 'index'])->setName('banner.index');
                $group->get('/create', [BannerController::class, 'create'])->setName('banner.create');
                $group->post('', [BannerController::class, 'insert'])->setName('banner.insert');
                $group->get('/{bn_id}', [BannerController::class, 'view'])->setName('banner.view');
                $group->post('/{bn_id}', [BannerController::class, 'update'])->setName('banner.update');
                $group->delete('/{bn_id}', [BannerController::class, 'delete'])->setName('banner.delete');
            })->add(AdminMenuAuthMiddleware::class);

            // 레이어팝업
            $group->group('/popup', function (RouteCollectorProxy $group) {
                $group->get('', [PopupController::class, 'index'])->setName('popup.index');
                $group->get('/create', [PopupController::class, 'create'])->setName('popup.create');
                $group->post('', [PopupController::class, 'insert'])->setName('popup.insert');
                $group->get('/{pu_id}', [PopupController::class, 'view'])->setName('popup.view');
                $group->post('/{pu_id}', [PopupController::class, 'update'])->setName('popup.update');
                $group->delete('/{pu_id}', [PopupController::class, 'delete'])->setName('popup.delete');
            })->add(AdminMenuAuthMiddleware::class);
        });

        // 회원        
        $group->group('/member', function (RouteCollectorProxy $group) {
            // 기본환경 설정
            $group->group('/config', function (RouteCollectorProxy $group) {
                $group->get('', [MemberConfigController::class, 'index'])->setName('member-config.index');
                $group->post('', [MemberConfigController::class, 'update'])->setName('member-config.update');
            })->add(AdminMenuAuthMiddleware::class);

            // 회원 포인트관리
            $group->group('/point', function (RouteCollectorProxy $group) {
                $group->get('[/{mb_id}]', [MemberController::class, 'index'])->setName('point.index');
                // $group->get('/create', [MemberController::class, 'create'])->setName('member.create');
                // $group->post('', [MemberController::class, 'insert'])->setName('member.insert');
                // $group->get('/{mb_id}', [MemberController::class, 'view'])->setName('member.view');
                // $group->post('/{mb_id}', [MemberController::class, 'update'])->setName('member.update');
                // $group->delete('/{mb_id}', [MemberController::class, 'delete'])->setName('member.delete');
            })->add(AdminMenuAuthMiddleware::class);

            // 회원관리
            $group->group('', function (RouteCollectorProxy $group) {
                $group->get('', [MemberController::class, 'index'])->setName('member.index');
                $group->get('/create', [MemberController::class, 'create'])->setName('member.create');
                $group->post('', [MemberController::class, 'insert'])->setName('member.insert');
                $group->get('/{mb_id}', [MemberController::class, 'view'])->setName('member.view');
                $group->post('/{mb_id}', [MemberController::class, 'update'])->setName('member.update');
                $group->delete('/{mb_id}', [MemberController::class, 'delete'])->setName('member.delete');
            })->add(AdminMenuAuthMiddleware::class);

            
        });
    })
        ->add(AdminMenuMiddleware::class)
        ->add(LoginAuthMiddleware::class);
})
    ->add(TemplateMiddleware::class)
    ->add(ConfigMiddleware::class);
