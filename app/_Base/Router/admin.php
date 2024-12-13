<?php

namespace App\Base\Router;

use App\Base\Controller\Admin\LoginController;
use App\Base\Controller\Admin\MainPageController;
use App\Base\Controller\Admin\MenuController;
use App\Base\Controller\Admin\ThemeController;
use App\Base\Controller\Admin\BannerController;
use App\Base\Controller\Admin\BoardConfigController;
use App\Base\Controller\Admin\BoardController;
use App\Base\Controller\Admin\DashboardController;
use App\Base\Controller\Admin\ConfigController;
use App\Base\Controller\Admin\NotificationController;
use App\Base\Controller\Admin\PermissionController;
use App\Base\Controller\Admin\SocialController;
use App\Base\Controller\Admin\ContentController;
use App\Base\Controller\Admin\FaqController;
use App\Base\Controller\Admin\LogoController;
use App\Base\Controller\Admin\MemberConfigController;
use App\Base\Controller\Admin\MemberController;
use App\Base\Controller\Admin\PluginController;
use App\Base\Controller\Admin\PointController;
use App\Base\Controller\Admin\PopupController;
use App\Base\Controller\Admin\QaConfigController;
use App\Base\Controller\Admin\QaController;
use App\Base\Controller\Admin\SocialLoginController;
use Core\Middleware\AdminMenuPermissionMiddleware;
use Core\Middleware\AdminMenuMiddleware;
use Core\Middleware\ConfigMiddleware;
use Core\Middleware\LoginAuthMiddleware;
use Core\Middleware\LoginMemberMiddleware;
use Core\Middleware\SuperAdminAuthMiddleware;
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
    // 소셜 로그인
    $group->get('/login/social/{provider}', [SocialLoginController::class, 'socialLogin'])->setName('login.social');
    $group->get('/login/social/{provider}/callback', [SocialLoginController::class, 'socialLoginCallback'])->setName('login.social.callback');
    // 로그아웃
    $group->get('/logout', [LoginController::class, 'Logout'])->setName('logout');

    // 관리자
    $group->group('', function (RouteCollectorProxy $group) {
        // 대시보드
        $group->get('[/dashboard]', [DashboardController::class, 'index'])->setName('admin.dashboard');

        // 기본환경
        $group->group('/config', function (RouteCollectorProxy $group) {

            // 기본환경 설정
            $group->group('/basic', function (RouteCollectorProxy $group) {
                $group->get('', [ConfigController::class, 'index'])->setName('admin.config.basic');
                $group->put('', [ConfigController::class, 'update'])->setName('admin.config.basic.update');
                $group->post('/mail/test', [ConfigController::class, 'sendMailTest'])->setName('admin.config.basic.mail.test');
            })->add(SuperAdminAuthMiddleware::class);

            // 운영진 설정
            $group->group('/permission', function (RouteCollectorProxy $group) {
                $group->get('', [PermissionController::class, 'index'])->setName('admin.config.permission');
                $group->post('', [PermissionController::class, 'insert'])->setName('admin.config.permission.insert');
                $group->put('/{mb_id}/{admin_menu_id:[0-9]+}', [PermissionController::class, 'update'])->setName('admin.config.permission.update');
                $group->delete('/{mb_id}/{admin_menu_id:[0-9]+}', [PermissionController::class, 'delete'])->setName('admin.config.permission.delete');
                $group->post('/list', [PermissionController::class, 'deleteList'])->setName('admin.config.permission.delete.list');
            })->add(SuperAdminAuthMiddleware::class);

            // API연동 설정
            $group->group('/api', function (RouteCollectorProxy $group) {
                $group->redirect('', 'api/social')->setName('admin.config.api');

                // 소셜 로그인
                $group->group('/social', function (RouteCollectorProxy $group) {
                    $group->get('', [SocialController::class, 'index'])->setName('admin.config.api.social');
                    $group->post('', [SocialController::class, 'insert'])->setName('admin.config.api.social.insert');
                    $group->put('/update', [SocialController::class, 'update'])->setName('admin.config.api.social.update');
                    $group->delete('/{provider}', [SocialController::class, 'delete'])->setName('admin.config.api.social.delete');
                });

                // 알림/메시징/메일 설정
                $group->group('/notification', function (RouteCollectorProxy $group) {
                    $group->get('', [NotificationController::class, 'index'])->setName('admin.config.api.notification');
                    $group->put('/update', [NotificationController::class, 'update'])->setName('admin.config.api.notification.update');
                });

            })->add(SuperAdminAuthMiddleware::class);
            
            // 캐시파일 관리
            $group->group('/cache', function (RouteCollectorProxy $group) {
                // $group->get('', [PermissionController::class, 'index'])->setName('admin.config.cache');
                // $group->delete('', [PermissionController::class, 'delete_list'])->setName('admin.config.cache.delete');
            });

            // 플러그인 관리
            $group->group('/plugin', function (RouteCollectorProxy $group) {
                $group->get('', [PluginController::class, 'index'])->setName('admin.config.plugin');
                $group->post('/install', [PluginController::class, 'install'])->setName('admin.config.plugin.install');
                $group->post('/{plugin}/activate', [PluginController::class, 'activate'])->setName('admin.config.plugin.activate');
                $group->post('/{plugin}/deactivate', [PluginController::class, 'deactivate'])->setName('admin.config.plugin.deactivate');
                $group->delete('/{plugin}/uninstall', [PluginController::class, 'uninstall'])->setName('admin.config.plugin.uninstall');
            });
        });

        // 디자인/UI
        $group->group('/design', function (RouteCollectorProxy $group) {
            // 테마
            $group->group('/theme', function (RouteCollectorProxy $group) {
                $group->get('', [ThemeController::class, 'index'])->setName('admin.design.theme');
                $group->post('/install', [ThemeController::class, 'install'])->setName('admin.design.theme.install');
                $group->post('/{theme}', [ThemeController::class, 'update'])->setName('admin.design.theme.update');
                $group->post('/{theme}/reset', [ThemeController::class, 'reset'])->setName('admin.design.theme.reset');
                $group->delete('/{theme}/uninstall', [ThemeController::class, 'uninstall'])->setName('admin.design.theme.uninstall');
            })->add(SuperAdminAuthMiddleware::class);

            // 로고
            $group->group('/logo', function (RouteCollectorProxy $group) {
                $group->get('', [LogoController::class, 'index'])->setName('admin.design.logo');
                $group->post('', [LogoController::class, 'update'])->setName('admin.design.logo.update');
            })->add(AdminMenuPermissionMiddleware::class);

            // 배너
            $group->group('/banner', function (RouteCollectorProxy $group) {
                $group->get('', [BannerController::class, 'index'])->setName('admin.design.banner');
                $group->get('/create', [BannerController::class, 'create'])->setName('admin.design.banner.create');
                $group->post('', [BannerController::class, 'insert'])->setName('admin.design.banner.insert');
                $group->get('/{bn_id}', [BannerController::class, 'view'])->setName('admin.design.banner.view');
                $group->put('/{bn_id}', [BannerController::class, 'update'])->setName('admin.design.banner.update');
                $group->put('/{bn_id}/enabled', [BannerController::class, 'toggleEnabled'])->setName('admin.design.banner.enabled');
                $group->delete('/{bn_id}', [BannerController::class, 'delete'])->setName('admin.design.banner.delete');
            })->add(AdminMenuPermissionMiddleware::class);

            // 레이어팝업
            $group->group('/popup', function (RouteCollectorProxy $group) {
                $group->get('', [PopupController::class, 'index'])->setName('admin.design.popup');
                $group->get('/create', [PopupController::class, 'create'])->setName('admin.design.popup.create');
                $group->post('', [PopupController::class, 'insert'])->setName('admin.design.popup.insert');
                $group->get('/{pu_id}', [PopupController::class, 'view'])->setName('admin.design.popup.view');
                $group->put('/{pu_id}', [PopupController::class, 'update'])->setName('admin.design.popup.update');
                $group->put('/{pu_id}/enabled', [PopupController::class, 'toggleEnabled'])->setName('admin.design.popup.enabled');
                $group->delete('/{pu_id}', [PopupController::class, 'delete'])->setName('admin.design.popup.delete');
            })->add(AdminMenuPermissionMiddleware::class);

            // 메인화면 설정
            $group->group('/mainpage', function (RouteCollectorProxy $group) {
                $group->get('', [MainPageController::class, 'index'])->setName('admin.design.mainpage');
                $group->post('', [MainPageController::class, 'insert'])->setName('admin.design.mainpage.insert');
                $group->get('/{id}', [MainPageController::class, 'get'])->setName('admin.design.mainpage.get');
                $group->post('/{id}', [MainPageController::class, 'update'])->setName('admin.design.mainpage.update');
                $group->post('/list/update', [MainPageController::class, 'updateList'])->setName('admin.design.mainpage.list.update');
                $group->delete('/{id}', [MainPageController::class, 'delete'])->setName('admin.design.mainpage.delete');
            });

            // 메뉴 설정
            $group->group('/menu', function (RouteCollectorProxy $group) {
                $group->get('', [MenuController::class, 'index'])->setName('admin.design.menu');
                $group->get('/{type}', [MenuController::class, 'getUrls'])->setName('admin.design.menu.urls');
                $group->post('', [MenuController::class, 'insert'])->setName('admin.design.menu.insert');
                $group->put('/{me_id}', [MenuController::class, 'update'])->setName('admin.design.menu.update');
                $group->delete('/{me_id}', [MenuController::class, 'delete'])->setName('admin.design.menu.delete');
                $group->post('/list/update', [MenuController::class, 'updateList'])->setName('admin.design.menu.list.update');
            })->add(AdminMenuPermissionMiddleware::class);
        });

        // 회원        
        $group->group('/member', function (RouteCollectorProxy $group) {
            // 기본환경 설정
            $group->group('/config', function (RouteCollectorProxy $group) {
                $group->redirect('', 'config/basic')->setName('admin.member.config');

                $group->get('/basic', [MemberConfigController::class, 'index'])->setName('admin.member.config.basic');
                $group->put('/basic', [MemberConfigController::class, 'update'])->setName('admin.member.config.basic.update');

                $group->get('/notification', [MemberConfigController::class, 'indexNotification'])->setName('admin.member.config.notification');
                $group->put('/notification', [MemberConfigController::class, 'updateNotification'])->setName('admin.member.config.notification.update');
            });

            // 1:1문의
            $group->group('/qa', function (RouteCollectorProxy $group) {
                $group->redirect('', 'qa/config')->setName('admin.member.qa');
                
                // 환경설정
                $group->group('/config', function (RouteCollectorProxy $group) {
                    $group->get('', [QaConfigController::class, 'index'])->setName('admin.member.qa.config');
                    $group->post('', [QaConfigController::class, 'update'])->setName('admin.member.qa.config.update');
                    $group->post('/template', [QaConfigController::class, 'updateBasicTemplate'])->setName('admin.member.qa.config.template');
                    $group->post('/category', [QaConfigController::class, 'createCategory'])->setName('admin.member.qa.config.category.create');
                    $group->get('/category/{id}', [QaConfigController::class, 'getCategory'])->setName('admin.member.qa.config.category');
                    $group->post('/category/{id}', [QaConfigController::class, 'updateCategory'])->setName('admin.member.qa.config.category.update');
                    $group->delete('/category/{id}', [QaConfigController::class, 'deleteCategory'])->setName('admin.member.qa.config.category.delete');
                });

                // 1:1문의 내용
                $group->group('/manage', function (RouteCollectorProxy $group) {
                    $group->get('', [QaController::class, 'index'])->setName('admin.member.qa.manage');
                    $group->get('/create', [QaController::class, 'create'])->setName('admin.member.qa.manage.create');
                    $group->post('', [QaController::class, 'insert'])->setName('admin.member.qa.manage.insert');
                    $group->get('/{id}', [QaController::class, 'view'])->setName('admin.member.qa.manage.view');
                    $group->put('/{id}', [QaController::class, 'updateQa'])->setName('admin.member.qa.manage.update');
                    $group->delete('/{id}', [QaController::class, 'delete'])->setName('admin.member.qa.manage.delete');
                });
            });

            // 포인트 관리
            $group->group('/point', function (RouteCollectorProxy $group) {
                $group->get('', [PointController::class, 'index'])->setName('admin.member.point');
                $group->post('', [PointController::class, 'insert'])->setName('admin.member.point.insert');
                $group->put('/{po_id}', [PointController::class, 'update'])->setName('admin.member.point.update');
                $group->delete('/{po_id}', [PointController::class, 'delete'])->setName('admin.member.point.delete');
                $group->post('/list/delete', [PointController::class, 'deleteList'])->setName('admin.member.point.delete.list');
            });

            // 회원관리
            $group->group('', function (RouteCollectorProxy $group) {
                $group->get('', [MemberController::class, 'index'])->setName('admin.member.manage');
                $group->get('/create', [MemberController::class, 'create'])->setName('admin.member.manage.create');
                $group->get('/search', [MemberController::class, 'searchMembers'])->setName('admin.member.manage.search');
                $group->post('', [MemberController::class, 'insert'])->setName('admin.member.manage.insert');
                $group->get('/{mb_id}', [MemberController::class, 'view'])->setName('admin.member.manage.view');
                $group->post('/{mb_id}', [MemberController::class, 'update'])->setName('admin.member.manage.update');
                $group->post('/list/update', [MemberController::class, 'updateList'])->setName('admin.member.manage.update.list');
                $group->post('/list/delete', [MemberController::class, 'deleteList'])->setName('admin.member.manage.delete.list');
                $group->delete('/{mb_id}', [MemberController::class, 'delete'])->setName('admin.member.manage.delete');
                $group->get('/{mb_id}/info', [MemberController::class, 'getMemberInfo'])->setName('admin.member.manage.info');
                $group->post('/{mb_id}/memo', [MemberController::class, 'insertMemo'])->setName('admin.member.manage.memo.insert');
                $group->delete('/{mb_id}/memo/{memo_id}', [MemberController::class, 'deleteMemo'])->setName('admin.member.manage.memo.delete');
                $group->post('/{mb_id}/notification', [MemberController::class, 'sendNotification'])->setName('admin.member.manage.notification');
                $group->delete('/{mb_id}/social/{provider}/unlink', [SocialLoginController::class, 'unlinkSocial'])->setName('admin.member.manage.social.unlink');
            });
        })->add(AdminMenuPermissionMiddleware::class);

        // 컨텐츠
        $group->group('/content', function (RouteCollectorProxy $group) {
            // 컨텐츠 목록을 ajax에서 조회
            $group->get('/list', [ContentController::class, 'getList'])->setName('admin.content.list');

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
                    $group->get('/{faq_id}', [FaqController::class, 'view'])->setName('admin.content.faq.view');
                    $group->post('/{faq_id}', [FaqController::class, 'update'])->setName('admin.content.faq.update');
                    $group->delete('/{faq_id}', [FaqController::class, 'delete'])->setName('admin.content.faq.delete');
                });
            })->add(AdminMenuPermissionMiddleware::class);
        });

        // 커뮤니티
        $group->group('/community', function (RouteCollectorProxy $group) {
            // 커뮤니티 설정
            $group->group('/config', function (RouteCollectorProxy $group) {
                $group->redirect('', 'config/basic')->setName('admin.community.config');
                // 기본환경설정
                $group->group('/basic', function (RouteCollectorProxy $group) {
                    $group->get('', [BoardConfigController::class, 'index'])->setName('admin.community.config.basic');
                    $group->put('', [BoardConfigController::class, 'update'])->setName('admin.community.config.basic.update');
                });
                // 알림/푸시 설정
                $group->group('/notification', function (RouteCollectorProxy $group) {
                    $group->get('', [BoardConfigController::class, 'indexNotification'])->setName('admin.community.config.notification');
                    $group->put('', [BoardConfigController::class, 'updateNotification'])->setName('admin.community.config.notification.update');
                });
            })->add(AdminMenuPermissionMiddleware::class);

            // 게시판 관리
            $group->group('/board', function (RouteCollectorProxy $group) {
                $group->get('', [BoardController::class, 'index'])->setName('admin.community.board');
                $group->get('/create', [BoardController::class, 'create'])->setName('admin.community.board.create');
                $group->post('', [BoardController::class, 'insert'])->setName('admin.community.board.insert');
                $group->get('/{board_id}', [BoardController::class, 'view'])->setName('admin.community.board.view');
                $group->put('/{board_id}', [BoardController::class, 'update'])->setName('admin.community.board.update');
                $group->put('/{board_id}/level', [BoardController::class, 'updateLevel'])->setName('admin.community.board.level.update');
                $group->delete('/{board_id}', [BoardController::class, 'delete'])->setName('admin.community.board.delete');
            })->add(AdminMenuPermissionMiddleware::class);
        });
    })
        ->add(AdminMenuMiddleware::class)
        ->add(LoginAuthMiddleware::class);
})
    ->add(LoginMemberMiddleware::class)
    ->add(ConfigMiddleware::class);
