<?php

namespace App\Admin\Controller;

use App\Admin\Model\UpdateThemeConfigRequest;
use App\Admin\Service\ThemeService;
use App\Config\ConfigService;
use Core\BaseController;
use Core\FileService;
use Core\Lib\UriHelper;
use Core\Validator\Validator;
use Slim\Exception\HttpNotFoundException;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class ThemeController extends BaseController
{
    private ConfigService $config_service;
    private FileService $file_service;
    private ThemeService $service;

    public function __construct(
        ConfigService $config_service,
        FileService $file_service,
        ThemeService $service,

    ) {
        $this->config_service = $config_service;
        $this->file_service = $file_service;
        $this->service = $service;
    }

    /**
     * 테마 목록 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');
        $banner['logo_header_width'] = $this->service->getImageWidth($request, $config['logo_header']);
        $banner['logo_footer_width'] = $this->service->getImageWidth($request, $config['logo_footer']);

        $response_data = [
            "config" => $config,
            "current_theme" => $config['cf_theme'],
            "themes" => $this->service->getInstalledThemes(),
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/theme_form.html', $response_data);
    }

    /**
     * 테마 설정 업데이트
     */
    public function update(Request $request, Response $response, string $theme): Response
    {
        if (!$this->service->existsTheme($theme)) {
            throw new HttpNotFoundException($request, '선택하신 테마가 설치되어 있지 않습니다.');
        }

        $this->config_service->update(['cf_theme' => $theme]);

        return $response->withJson(['message' => '테마가 변경되었습니다.']);
    }

    /**
     * 테마 설정 리셋
     */
    public function reset(Response $response): Response
    {
        $this->config_service->update(['cf_theme' => '']);

        return $response->withJson(['message' => '테마가 사용안함 처리되었습니다.']);
    }

    /**
     * 테마의 기타설정 업데이트
     */
    public function updateInfo(Request $request, Response $response, UpdateThemeConfigRequest $data): Response
    {
        $config = $request->getAttribute('config');

        if (
            $data->logo_header_del
            || (Validator::isUploadedFile($data->logo_header_file) && $config['logo_header'])
        ) {
            $this->file_service->deleteFile(UriHelper::getBasePath($request) . $config['logo_header']);
            $data->logo_header = null;
        }
        if (
            $data->logo_footer_del
            || (Validator::isUploadedFile($data->logo_footer_file) && $config['logo_footer'])
        ) {
            $this->file_service->deleteFile(UriHelper::getBasePath($request) . $config['logo_footer']);
            $data->logo_footer = null;
        }
        unset($data->logo_header_del);
        unset($data->logo_footer_del);

        $this->service->uploadImage($request, $data);
        $this->config_service->update($data->publics());

        return $this->redirectRoute($request, $response, 'admin.design.theme');
    }
}
