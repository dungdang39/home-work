<?php

namespace App\Admin\Controller;

use App\Admin\Service\AdminMenuService;
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

        return $view->render($response, '/admin/dashboard.php');
    }
}
