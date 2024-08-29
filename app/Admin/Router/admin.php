<?php

namespace App\Login\Router;

use App\Admin\Controller\AdminMenuPermissionController;
use App\Admin\Controller\ConfigController;
use App\Admin\Controller\LoginController;
use App\Admin\Controller\DashboardController;
use App\Admin\Controller\MenuController;
use App\Admin\Controller\ThemeController;
use App\Banner\Controller\BannerController;
use App\Member\Controller\MemberConfigController;
use App\Member\Controller\MemberController;
use App\Popup\Controller\PopupController;
use App\Qa\Controller\QaConfigController;
use App\Qa\Controller\QaController;
use Core\Middleware\AdminMenuPermissionMiddleware;
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
    $group->get('/login', [LoginController::class, 'adminLoginPage'])->setName('admin.login');
    $group->post('/login', [LoginController::class, 'Login'])->setName('login');

    // 로그아웃
    $group->get('/logout', [LoginController::class, 'Logout'])->setName('logout');

    // 관리자
    $group->group('', function (RouteCollectorProxy $group) {
        // 대시보드
        $group->get('[/dashboard]', [DashboardController::class, 'index'])->setName('admin.dashboard');

        // 기본환경 설정
        $group->group('/config', function (RouteCollectorProxy $group) {
            $group->get('', [ConfigController::class, 'index'])->setName('admin.config');
            $group->post('', [ConfigController::class, 'update'])->setName('admin.config.update');
        })->add(SuperAdminAuthMiddleware::class);

        // 운영진 설정
        $group->group('/administrator', function (RouteCollectorProxy $group) {
            $group->get('', [AdminMenuPermissionController::class, 'index'])->setName('admin.administrator');
            $group->post('', [AdminMenuPermissionController::class, 'insert'])->setName('admin.administrator.insert');
            $group->put('/{mb_id}/{admin_menu_id}', [AdminMenuPermissionController::class, 'update'])->setName('admin.administrator.update');
            $group->delete('/{mb_id}/{admin_menu_id}', [AdminMenuPermissionController::class, 'delete'])->setName('admin.administrator.delete');
            $group->delete('/list', [AdminMenuPermissionController::class, 'delete_list'])->setName('admin.administrator.delete-list');
        })->add(SuperAdminAuthMiddleware::class);

        // 메뉴 설정
        $group->group('/menu', function (RouteCollectorProxy $group) {
            $group->get('', [MenuController::class, 'index'])->setName('admin.menu');
            $group->get('/{type}', [MenuController::class, 'getUrls'])->setName('admin.menu.urls');
            $group->post('', [MenuController::class, 'insert'])->setName('admin.menu.insert');
            $group->put('/{me_id}', [MenuController::class, 'update'])->setName('admin.menu.update');
            $group->delete('', [MenuController::class, 'delete'])->setName('admin.menu.delete');
        })->add(AdminMenuPermissionMiddleware::class);

        // 디자인/UI
        $group->group('/design', function (RouteCollectorProxy $group) {
            // 테마
            $group->group('/theme', function (RouteCollectorProxy $group) {
                $group->get('', [ThemeController::class, 'index'])->setName('admin.theme');
                $group->post('/{theme}', [ThemeController::class, 'update'])->setName('admin.theme.update');
            })->add(SuperAdminAuthMiddleware::class);

            // 배너
            $group->group('/banner', function (RouteCollectorProxy $group) {
                $group->get('', [BannerController::class, 'index'])->setName('admin.banner');
                $group->get('/create', [BannerController::class, 'create'])->setName('admin.banner.create');
                $group->post('', [BannerController::class, 'insert'])->setName('admin.banner.insert');
                $group->get('/{bn_id}', [BannerController::class, 'view'])->setName('admin.banner.view');
                $group->post('/{bn_id}', [BannerController::class, 'update'])->setName('admin.banner.update');
                $group->delete('/{bn_id}', [BannerController::class, 'delete'])->setName('admin.banner.delete');
            })->add(AdminMenuPermissionMiddleware::class);

            // 레이어팝업
            $group->group('/popup', function (RouteCollectorProxy $group) {
                $group->get('', [PopupController::class, 'index'])->setName('admin.popup');
                $group->get('/create', [PopupController::class, 'create'])->setName('admin.popup.create');
                $group->post('', [PopupController::class, 'insert'])->setName('admin.popup.insert');
                $group->get('/{pu_id}', [PopupController::class, 'view'])->setName('admin.popup.view');
                $group->post('/{pu_id}', [PopupController::class, 'update'])->setName('admin.popup.update');
                $group->delete('/{pu_id}', [PopupController::class, 'delete'])->setName('admin.popup.delete');
            })->add(AdminMenuPermissionMiddleware::class);
        });

        // 회원        
        $group->group('/member', function (RouteCollectorProxy $group) {
            // 기본환경 설정
            $group->group('/config', function (RouteCollectorProxy $group) {
                $group->get('', [MemberConfigController::class, 'index'])->setName('admin.member-config');
                $group->post('', [MemberConfigController::class, 'update'])->setName('admin.member-config.update');
            });

            // 회원 포인트관리
            $group->group('/point', function (RouteCollectorProxy $group) {
                $group->get('[/{po_id}]', [MemberController::class, 'index'])->setName('admin.point');
                $group->post('', [MemberController::class, 'insert'])->setName('admin.point.insert');
                $group->post('/{po_id}', [MemberController::class, 'update'])->setName('admin.point.update');
                $group->delete('/{po_id}', [MemberController::class, 'delete'])->setName('admin.point.delete');
            });

            // 회원관리
            $group->group('', function (RouteCollectorProxy $group) {
                $group->get('', [MemberController::class, 'index'])->setName('admin.member');
                $group->get('/create', [MemberController::class, 'create'])->setName('admin.member.create');
                $group->post('', [MemberController::class, 'insert'])->setName('admin.member.insert');
                $group->get('/{mb_id}', [MemberController::class, 'view'])->setName('admin.member.view');
                $group->post('/{mb_id}', [MemberController::class, 'update'])->setName('admin.member.update');
                $group->delete('/{mb_id}', [MemberController::class, 'delete'])->setName('admin.member.delete');
                $group->get('/{mb_id}/info', [MemberController::class, 'getMemberInfo'])->setName('admin.member.info');
            });
        })->add(AdminMenuPermissionMiddleware::class);

        // 회원 > 1:1문의
        $group->group('/qa', function (RouteCollectorProxy $group) {
            // 설정
            $group->group('/config', function (RouteCollectorProxy $group) {
                $group->get('', [QaConfigController::class, 'index'])->setName('admin.qa-config');
                $group->post('', [QaConfigController::class, 'update'])->setName('admin.qa-config.update');
            });

            // 1:1문의 내용 (미구현)
            $group->group('', function (RouteCollectorProxy $group) {
                $group->get('', [QaController::class, 'index'])->setName('admin.qa');
                $group->get('/create', [QaController::class, 'create'])->setName('admin.qa.create');
                $group->post('', [QaController::class, 'insert'])->setName('admin.qa.insert');
                $group->get('/{qa_id}', [QaController::class, 'view'])->setName('admin.qa.view');
                $group->post('/{qa_id}', [QaController::class, 'update'])->setName('admin.qa.update');
                $group->delete('/{qa_id}', [QaController::class, 'delete'])->setName('admin.qa.delete');
            });
            
        })->add(AdminMenuPermissionMiddleware::class);

        // 컨텐츠 > 컨텐츠 관리
        $group->group('/content', function (RouteCollectorProxy $group) {
            $group->get('', [QaController::class, 'index'])->setName('admin.content');
            $group->get('/create', [QaController::class, 'create'])->setName('admin.content.create');
            $group->post('', [QaController::class, 'insert'])->setName('admin.content.insert');
            $group->get('/{co_id}', [QaController::class, 'view'])->setName('admin.content.view');
            $group->post('/{co_id}', [QaController::class, 'update'])->setName('admin.content.update');
            $group->delete('/{co_id}', [QaController::class, 'delete'])->setName('admin.content.delete');
        })->add(AdminMenuPermissionMiddleware::class);

        // 컨텐츠 > FAQ 관리
        $group->group('/faq', function (RouteCollectorProxy $group) {
            $group->get('', [QaController::class, 'index'])->setName('admin.faq');
            $group->get('/create', [QaController::class, 'create'])->setName('admin.faq.create');
            $group->post('', [QaController::class, 'insert'])->setName('admin.faq.insert');
            $group->get('/{fa_id}', [QaController::class, 'view'])->setName('admin.faq.view');
            $group->post('/{fa_id}', [QaController::class, 'update'])->setName('admin.faq.update');
            $group->delete('/{fa_id}', [QaController::class, 'delete'])->setName('admin.faq.delete');
        })->add(AdminMenuPermissionMiddleware::class);
    })
        ->add(AdminMenuMiddleware::class)
        ->add(LoginAuthMiddleware::class);
})
    ->add(TemplateMiddleware::class)
    ->add(ConfigMiddleware::class);
