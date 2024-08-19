<?php

namespace App\Admin\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

class ThemeController
{
    /**
     * 테마 목록 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        $theme_path = $request->getAttribute('theme_path');
        $config = $request->getAttribute('config');

        $template = $theme_path . '/admin/theme_list.html';
        $response_data = [
            "config" => $config,
        ];
        return $view->render($response, $template, $response_data);
    }

    /**
     * 테마 상세보기
     */
    public function view(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        $theme_path = $request->getAttribute('theme_path');
        $config = $request->getAttribute('config');

        // $theme = $request->getAttribute('theme');
        // $theme_path = $theme_path . '/' . $theme;

        $template = $theme_path . '/admin/theme_view.html';
        $response_data = [
            "config" => $config
        ];
        return $view->render($response, $template, $response_data);
    }

    /**
     * 테마 설정 업데이트
     */
    public function update(Request $request, Response $response): Response
    {
        try {
            $request_body = $request->getParsedBody();

            // $data = new UpdateConfigRequest($request_body);
            // $this->service->update($data->toArray());
            // run_event('admin_config_form_update');
    
            $routeContext = RouteContext::fromRequest($request);
            $redirect_url = $routeContext->getRouteParser()->urlFor('theme.index');
            return $response->withHeader('Location', $redirect_url)->withStatus(302);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
