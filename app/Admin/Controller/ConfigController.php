<?php

namespace App\Admin\Controller;

use app\Config\ConfigService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class ConfigController
{
    private ConfigService $service;

    public function __construct(
        ConfigService $service
    ) {
        $this->service = $service;
    }

    public function index(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        $theme_path = $request->getAttribute('theme_path');
        $config = $request->getAttribute('config');

        $template = $theme_path . '/admin/config_form.html';
        $response_data = [
            "config" => $config
        ];
        return $view->render($response, $template, $response_data);
    }

    public function update(Request $request, Response $response): Response
    {
        $request_body = $request->getParsedBody();
        print_r($request_body);
        exit;
        $this->service->update($config);

        return $response->withHeader('Location', '/admin/config');
    }
}
