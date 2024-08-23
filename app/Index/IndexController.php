<?php

namespace App\Index;

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
        $view = Twig::fromRequest($request);

        return $view->render($response, 'index.php', [
            'name' => 'gnuboard',
        ]);
    }
}
