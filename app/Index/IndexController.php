<?php

namespace App\Index;

use Core\AppConfig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
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
