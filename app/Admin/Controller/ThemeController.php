<?php

namespace App\Admin\Controller;

use App\Admin\Service\ThemeService;
use App\Config\ConfigService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

class ThemeController
{
    private ConfigService $config_service;
    private ThemeService $theme_service;

    public function __construct(
        ConfigService $config_service,
        ThemeService $theme_service
    ) {
        $this->config_service = $config_service;
        $this->theme_service = $theme_service;
    }

    /**
     * 테마 목록 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');
        $current_theme = $this->config_service->getTheme();
        $themes = $this->theme_service->getThemes();

        // 설정된 테마가 존재하지 않는다면 테마설정 초기화
        $exists_theme = $this->theme_service->existsTheme($config['cf_theme']);
        if (isset($config['cf_theme']) && !$exists_theme) {
            $this->config_service->update(['cf_theme' => '']);
        }
        $response_data = [
            "current_theme" => $current_theme,
            "themes" => $themes,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/theme_list.html', $response_data);
    }

    /**
     * 테마 상세보기
     */
    public function view(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');

        // $theme = $request->getAttribute('theme');

        $response_data = [
            "config" => $config
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/theme_view.html', $response_data);
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
