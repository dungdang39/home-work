<?php

namespace App\Config\Router;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Views\Twig;

/**
 * @var App $app
 */
$app->get('/config', function (Request $request, Response $response) {
    $view = Twig::fromRequest($request);

    return $view->render($response, 'theme/basic/index.php', [
        'name' => 'saf',
    ]);
});
