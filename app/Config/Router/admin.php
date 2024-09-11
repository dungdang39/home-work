<?php

namespace App\Config\Router;

use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
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
