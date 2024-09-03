<?php

namespace App\Login\Router;


use App\Admin\Controller\AdminMenuPermissionController;
use App\Admin\Controller\ConfigController;
use App\Admin\Controller\LoginController;
use App\Admin\Controller\DashboardController;
use App\Admin\Controller\MenuController;
use App\Admin\Controller\SocialController;
use App\Admin\Controller\ThemeController;
use App\Banner\Controller\BannerController;
use App\Content\ContentController;
use App\Faq\FaqController;
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

        // 기본환경
        $group->group('/setting', function (RouteCollectorProxy $group) {

            // 기본환경 설정
            $group->group('/config', function (RouteCollectorProxy $group) {
                $group->get('', [ConfigController::class, 'index'])->setName('admin.setting.config');
                $group->post('', [ConfigController::class, 'update'])->setName('admin.setting.config.update');
            })->add(SuperAdminAuthMiddleware::class);

            // 운영진 설정
            $group->group('/permission', function (RouteCollectorProxy $group) {
                $group->get('', [AdminMenuPermissionController::class, 'index'])->setName('admin.setting.permission');
                $group->post('', [AdminMenuPermissionController::class, 'insert'])->setName('admin.setting.permission.insert');
                $group->put('/{mb_id}/{admin_menu_id}', [AdminMenuPermissionController::class, 'update'])->setName('admin.setting.permission.update');
                $group->delete('/{mb_id}/{admin_menu_id}', [AdminMenuPermissionController::class, 'delete'])->setName('admin.setting.permission.delete');
                $group->delete('/list', [AdminMenuPermissionController::class, 'delete_list'])->setName('admin.setting.permission.delete-list');
            })->add(SuperAdminAuthMiddleware::class);

            // API연동 설정
            $group->group('/api', function (RouteCollectorProxy $group) {
                $group->redirect('', 'api/social')->setName('admin.setting.api');

                // 소셜 로그인
                $group->group('/social', function (RouteCollectorProxy $group) {
                    $group->get('', [SocialController::class, 'index'])->setName('admin.setting.api.social');
                    $group->post('', [SocialController::class, 'insert'])->setName('admin.setting.api.social.insert');
                    $group->post('/update', [SocialController::class, 'update'])->setName('admin.setting.api.social.update');
                    $group->post('/delete', [SocialController::class, 'delete'])->setName('admin.setting.api.social.delete');
                });
            })->add(SuperAdminAuthMiddleware::class);
        });

        // 메뉴
        $group->group('/menu', function (RouteCollectorProxy $group) {
            // 메뉴 설정
            $group->group('/menu', function (RouteCollectorProxy $group) {
                $group->get('', [MenuController::class, 'index'])->setName('admin.menu.manage');
                $group->get('/{type}', [MenuController::class, 'getUrls'])->setName('admin.menu.manage.urls');
                $group->post('', [MenuController::class, 'insert'])->setName('admin.menu.manage.insert');
                $group->put('/{me_id}', [MenuController::class, 'update'])->setName('admin.menu.manage.update');
                $group->delete('', [MenuController::class, 'delete'])->setName('admin.menu.manage.delete');
            })->add(AdminMenuPermissionMiddleware::class);
        });

        // 디자인/UI
        $group->group('/design', function (RouteCollectorProxy $group) {
            // 테마
            $group->group('/theme', function (RouteCollectorProxy $group) {
                $group->get('', [ThemeController::class, 'index'])->setName('admin.design.theme');
                $group->post('/{theme}', [ThemeController::class, 'update'])->setName('admin.design.theme.update');
            })->add(SuperAdminAuthMiddleware::class);

            // 배너
            $group->group('/banner', function (RouteCollectorProxy $group) {
                $group->get('', [BannerController::class, 'index'])->setName('admin.design.banner');
                $group->get('/create', [BannerController::class, 'create'])->setName('admin.design.banner.create');
                $group->post('', [BannerController::class, 'insert'])->setName('admin.design.banner.insert');
                $group->get('/{bn_id}', [BannerController::class, 'view'])->setName('admin.design.banner.view');
                $group->post('/{bn_id}', [BannerController::class, 'update'])->setName('admin.design.banner.update');
                $group->delete('/{bn_id}', [BannerController::class, 'delete'])->setName('admin.design.banner.delete');
            })->add(AdminMenuPermissionMiddleware::class);

            // 레이어팝업
            $group->group('/popup', function (RouteCollectorProxy $group) {
                $group->get('', [PopupController::class, 'index'])->setName('admin.design.popup');
                $group->get('/create', [PopupController::class, 'create'])->setName('admin.design.popup.create');
                $group->post('', [PopupController::class, 'insert'])->setName('admin.design.popup.insert');
                $group->get('/{pu_id}', [PopupController::class, 'view'])->setName('admin.design.popup.view');
                $group->post('/{pu_id}', [PopupController::class, 'update'])->setName('admin.design.popup.update');
                $group->delete('/{pu_id}', [PopupController::class, 'delete'])->setName('admin.design.popup.delete');
            })->add(AdminMenuPermissionMiddleware::class);
        });

        // 회원        
        $group->group('/member', function (RouteCollectorProxy $group) {
            // 기본환경 설정
            $group->group('/config', function (RouteCollectorProxy $group) {
                $group->get('', [MemberConfigController::class, 'index'])->setName('admin.member.config');
                $group->post('', [MemberConfigController::class, 'update'])->setName('admin.member.config.update');
            });

            // 1:1문의
            $group->group('/qa', function (RouteCollectorProxy $group) {
                // 환경설정
                $group->group('/config', function (RouteCollectorProxy $group) {
                    $group->get('', [QaConfigController::class, 'index'])->setName('admin.member.qa.config');
                    $group->post('', [QaConfigController::class, 'update'])->setName('admin.member.qa.config.update');
                });

                // 알림/푸시 설정
                $group->group('/notification', function (RouteCollectorProxy $group) {
                    $group->get('', [QaConfigController::class, 'index'])->setName('admin.member.qa.notification');
                    $group->post('', [QaConfigController::class, 'update'])->setName('admin.member.qa.notification.update');
                });

                // 1:1문의 내용 (미구현)
                $group->group('', function (RouteCollectorProxy $group) {
                    $group->get('', [QaController::class, 'index'])->setName('admin.member.qa');
                    $group->get('/create', [QaController::class, 'create'])->setName('admin.member.qa.create');
                    $group->post('', [QaController::class, 'insert'])->setName('admin.member.qa.insert');
                    $group->get('/{qa_id}', [QaController::class, 'view'])->setName('admin.member.qa.view');
                    $group->post('/{qa_id}', [QaController::class, 'update'])->setName('admin.member.qa.update');
                    $group->delete('/{qa_id}', [QaController::class, 'delete'])->setName('admin.member.qa.delete');
                });
            });

            // 포인트 관리
            $group->group('/point', function (RouteCollectorProxy $group) {
                $group->get('[/{po_id}]', [MemberController::class, 'index'])->setName('admin.member.point');
                $group->post('', [MemberController::class, 'insert'])->setName('admin.member.point.insert');
                $group->post('/{po_id}', [MemberController::class, 'update'])->setName('admin.member.point.update');
                $group->delete('/{po_id}', [MemberController::class, 'delete'])->setName('admin.member.point.delete');
            });

            // 회원관리
            $group->group('', function (RouteCollectorProxy $group) {
                $group->get('', [MemberController::class, 'index'])->setName('admin.member.manage');
                $group->get('/create', [MemberController::class, 'create'])->setName('admin.member.manage.create');
                $group->post('', [MemberController::class, 'insert'])->setName('admin.member.manage.insert');
                $group->get('/{mb_id}', [MemberController::class, 'view'])->setName('admin.member.manage.view');
                $group->post('/{mb_id}', [MemberController::class, 'update'])->setName('admin.member.manage.update');
                $group->delete('/{mb_id}', [MemberController::class, 'delete'])->setName('admin.member.manage.delete');
                $group->get('/{mb_id}/info', [MemberController::class, 'getMemberInfo'])->setName('admin.member.manage.info');
            });
        })->add(AdminMenuPermissionMiddleware::class);

        // 컨텐츠
        $group->group('/content', function (RouteCollectorProxy $group) {
            // 컨텐츠 관리
            $group->group('/manage', function (RouteCollectorProxy $group) {
                $group->get('', [ContentController::class, 'index'])->setName('admin.content.manage');
                $group->get('/create', [ContentController::class, 'create'])->setName('admin.content.manage.create');
                $group->post('', [ContentController::class, 'insert'])->setName('admin.content.manage.insert');
                $group->get('/{code}', [ContentController::class, 'view'])->setName('admin.content.manage.view');
                $group->post('/{code}', [ContentController::class, 'update'])->setName('admin.content.manage.update');
                $group->delete('/{code}', [ContentController::class, 'delete'])->setName('admin.content.manage.delete');
            })->add(AdminMenuPermissionMiddleware::class);

            // FAQ 관리
            $group->group('/faq-category', function (RouteCollectorProxy $group) {
                $group->get('', [FaqController::class, 'index'])->setName('admin.content.faq');
                $group->post('', [FaqController::class, 'insertCategory'])->setName('admin.content.faq.category.insert');
                $group->post('/{faq_category_id}', [FaqController::class, 'updateCategory'])->setName('admin.content.faq.category.update');
                $group->delete('/{faq_category_id}', [FaqController::class, 'deleteCategory'])->setName('admin.content.faq.category.delete');

                $group->group('/{faq_category_id}/faq', function (RouteCollectorProxy $group) {
                    $group->get('', [FaqController::class, 'list'])->setName('admin.content.faq.list');
                    $group->get('/create', [FaqController::class, 'create'])->setName('admin.content.faq.create');
                    $group->post('', [FaqController::class, 'insert'])->setName('admin.content.faq.insert');
                    $group->get('/{id}', [FaqController::class, 'view'])->setName('admin.content.faq.view');
                    $group->post('/{id}', [FaqController::class, 'update'])->setName('admin.content.faq.update');
                    $group->delete('/{id}', [FaqController::class, 'delete'])->setName('admin.content.faq.delete');
                });
            })->add(AdminMenuPermissionMiddleware::class);
        });
    })
        ->add(AdminMenuMiddleware::class)
        ->add(LoginAuthMiddleware::class);
})
    ->add(TemplateMiddleware::class)
    ->add(ConfigMiddleware::class);
