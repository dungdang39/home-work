<?php

namespace App\Admin\Controller;

use App\Admin\Model\UpdateThemeConfigRequest;
use App\Admin\Service\ThemeService;
use App\Config\ConfigService;
use Core\BaseController;
use DI\Container;
use Exception;
use Slim\Exception\HttpNotFoundException;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class ThemeController extends BaseController
{
    private ConfigService $config_service;
    private ThemeService $service;

    public function __construct(
        Container $container,
        ConfigService $config_service,
        ThemeService $service
    ) {
        parent::__construct($container);

        $this->config_service = $config_service;
        $this->service = $service;
    }

    /**
     * 테마 목록 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');
        $themes = $this->service->getThemes();

        // 설정된 테마가 존재하지 않는다면 테마설정 초기화
        $exists_theme = $this->service->existsTheme($config['cf_theme']);
        if (isset($config['cf_theme']) && !$exists_theme) {
            $this->config_service->update(['cf_theme' => '']);
        }
        $response_data = [
            "current_theme" => $config['cf_theme'],
            "themes" => $themes,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/theme_form.html', $response_data);
    }

    /**
     * 테마 설정 업데이트
     */
    public function update(Request $request, Response $response, string $theme): Response
    {
        $update_type = $request->getParsedBody()['type'] ?? null;

        if ($update_type === 'reset') {
            $this->config_service->update(['cf_theme' => '']);
            return $response->withJson(['message' => '테마가 사용안함 처리되었습니다.']);
        }

        if (!$this->service->existsTheme($theme)) {
            throw new HttpNotFoundException($request, '선택하신 테마가 설치되어 있지 않습니다.');
        }

        $this->config_service->update(['cf_theme' => $theme]);

        return $response->withJson(['message' => '테마가 변경되었습니다.']);
    }

    /**
     * 테마의 기타설정 업데이트
     */
    public function updateInfo(Request $request, Response $response): Response
    {
        try {
            $request_body = $request->getParsedBody();
            $request_files = $request->getUploadedFiles();
            $data = new UpdateThemeConfigRequest($request_body, $request_files);

            // 파일 업로드 이후
            unset($data->logo_header_file);
            unset($data->logo_footer_file);

            $this->config_service->update($data->toArray());
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        return $this->redirectRoute($request, $response, 'admin.design.theme');
    }
}
