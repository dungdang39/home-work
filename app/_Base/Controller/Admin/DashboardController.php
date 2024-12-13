<?php

namespace App\Base\Controller\Admin;

use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class DashboardController
{
    public function index(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/dashboard.html');
    }
}
