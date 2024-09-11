<?php

namespace App\Admin\Controller;

use App\Admin\Service\AdminMenuService;
use Core\BaseController;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

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
        return $view->render($response, '/admin/admin_menu_form.php');
    }
}
