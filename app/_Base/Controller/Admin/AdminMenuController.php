<?php

namespace App\Base\Controller\Admin;

use App\Base\Service\AdminMenuService;
use Core\BaseController;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;


/**
 * 관리자 메뉴 관리 
 * - Controller만 존재하며, Service, Model은 존재하지 않음 (추후 개발 필요)
 */
class AdminMenuController extends BaseController
{
    private AdminMenuService $menu_service;

    public function __construct(
        AdminMenuService $menu_service
    ) {
        $this->menu_service = $menu_service;
    }

    public function index(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/admin_menu_form.php');
    }
}
