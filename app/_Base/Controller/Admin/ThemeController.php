<?php

namespace App\Base\Controller\Admin;

use App\Base\Model\Admin\InstallThemeRequest;
use App\Base\Service\ThemeService;
use App\Base\Service\ConfigService;
use Core\BaseController;
use Core\FileService;
use Core\Lib\FlashMessage;
use DI\Container;
use Slim\Exception\HttpNotFoundException;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class ThemeController extends BaseController
{
    private ConfigService $config_service;
    private FileService $file_service;
    private FlashMessage $flash;
    private ThemeService $service;

    public function __construct(
        Container $container,
        ConfigService $config_service,
        FileService $file_service,
        ThemeService $service,

    ) {
        $this->flash = $container->get(FlashMessage::class);
        $this->config_service = $config_service;
        $this->file_service = $file_service;
        $this->service = $service;
    }

    /**
     * 테마 목록 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $response_data = [
            'current_theme' => $this->config_service->getTheme(),
            'themes' => $this->service->getInstalledThemes(),
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/design/theme_list.html', $response_data);
    }

    /**
     * 테마 설정 업데이트
     */
    public function update(Request $request, Response $response, string $theme): Response
    {
        if (!$this->service->existsTheme($theme)) {
            throw new HttpNotFoundException($request, '선택하신 테마가 설치되어 있지 않습니다.');
        }

        $this->config_service->update('design', 'theme', $theme);

        return $response->withJson(['message' => '테마가 변경되었습니다.']);
    }

    /**
     * 테마 설정 리셋
     */
    public function reset(Response $response): Response
    {
        $this->config_service->update('design', 'theme', '');

        return $response->withJson(['message' => '테마가 사용안함 처리되었습니다.']);
    }

    /**
     * 테마 설치
     */
    public function install(Request $request, Response $response, InstallThemeRequest $request_data): Response
    {
        $uploaded_file = $request_data->theme_file;

        $this->service->extractTheme($uploaded_file);

        $theme_folder_name = pathinfo($uploaded_file->getClientFilename(), PATHINFO_FILENAME);
        $this->service->checkRequiredFiles($theme_folder_name);

        $this->flash->setMessage('테마가 설치되었습니다.');

        return $this->redirectRoute($request, $response, 'admin.design.theme');
    }

    /**
     * 테마 삭제
     */
    public function uninstall(Request $request, Response $response, string $theme): Response
    {
        if (!$this->service->existsTheme($theme)) {
            throw new HttpNotFoundException($request, '선택하신 테마가 설치되어 있지 않습니다.');
        }
        if ($theme === $this->config_service->getTheme()) {
            throw new HttpNotFoundException($request, '선택하신 테마는 현재 사용중인 테마입니다.');
        }
        if ($theme === $this->service::DEFAULT_THEME) {
            throw new HttpNotFoundException($request, '기본 테마는 삭제할 수 없습니다.');
        }

        $base_path = $request->getAttribute('base_path');
        $theme_dir = join(DIRECTORY_SEPARATOR, [$base_path, $this->service::DIRECTORY, $theme]);
        $this->file_service->clearDirectory($theme_dir);

        return $response->withJson([
            'result' => 'success',
            'message' => '테마가 삭제되었습니다.',
        ]);
    }
}
