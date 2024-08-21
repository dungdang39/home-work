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
        $themes = $this->theme_service->getThemes();

        // 설정된 테마가 존재하지 않는다면 테마설정 초기화
        $exists_theme = $this->theme_service->existsTheme($config['cf_theme']);
        if (isset($config['cf_theme']) && !$exists_theme) {
            $this->config_service->update(['cf_theme' => '']);
        }
        $response_data = [
            "current_theme" => $config['cf_theme'],
            "themes" => $themes,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/theme_list.html', $response_data);
    }

    /**
     * 테마 설정 업데이트
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $theme = $args['theme'];
            $update_type = $request->getParsedBody()['type'] ?? null;

            if ($update_type === 'reset') {
                $this->config_service->update(['cf_theme' => '']);
                return api_response_json($response, [
                    'result' => 'success',
                    'message' => '테마가 사용안함 처리되었습니다.',
                ], 200);
            }
    
            if (!$this->theme_service->existsTheme($theme)) {
                return api_response_json($response, [
                    'result' => 'error',
                    'message' => '선택하신 테마가 설치되어 있지 않습니다.',
                ], 400);
            }
    
            $this->config_service->update(['cf_theme' => $theme]);
            return api_response_json($response, [
                'result' => 'success',
                'message' => '테마가 변경되었습니다.',
            ], 200);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
