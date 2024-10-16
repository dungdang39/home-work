<?php

namespace App\Base\Controller;

use Core\AppConfig;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class IndexController
{
    public function __construct()
    {
    }

    public function index(Request $request, Response $response): Response
    {
        $response_data = [
            "name" => AppConfig::getInstance()->get('APP_NAME'),
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, 'index.html', $response_data);
    }
}
