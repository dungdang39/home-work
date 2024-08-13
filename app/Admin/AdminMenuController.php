<?php

namespace App\Admin;

use App\Admin\AdminMenuService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class DashboardController
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
        $theme_path = $request->getAttribute('theme_path');

        // @todo 로그인 체크

        return $view->render($response, $theme_path . '/admin/dashboard.php');
    }
}
