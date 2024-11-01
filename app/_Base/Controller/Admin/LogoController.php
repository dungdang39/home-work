<?php

namespace App\Base\Controller\Admin;

use App\Base\Model\Admin\UpdateLogoRequest;
use App\Base\Service\ConfigService;
use Core\BaseController;
use Core\FileService;
use Core\ImageService;
use Core\Validator\Validator;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class LogoController extends BaseController
{
    private ConfigService $config_service;
    private FileService $file_service;
    private ImageService $image_service;

    public function __construct(
        ConfigService $config_service,
        FileService $file_service,
        ImageService $image_service,

    ) {
        $this->config_service = $config_service;
        $this->file_service = $file_service;
        $this->image_service = $image_service;
    }

    /**
     * 로고 관리 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $configs = $this->config_service->getConfigs('design');
        $logo_header = $configs['logo_header'] ?? '';
        $logo_footer = $configs['logo_footer'] ?? '';

        $response_data = [
            'configs' => $configs,
            'logo_header_width' => $this->image_service->getImageWidth($request, $logo_header),
            'logo_footer_width' => $this->image_service->getImageWidth($request, $logo_footer),
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/design/logo_form.html', $response_data);
    }

    /**
     * 로고 설정 업데이트
     */
    public function update(Request $request, Response $response, UpdateLogoRequest $data): Response
    {
        $app_config = $request->getAttribute('app_config');
        $configs = $request->getAttribute('configs');

        $folder = $app_config->get('LOGO_DIR');
        if ($data->logo_header_del || Validator::isUploadedFile($data->logo_header_file)) {
            $this->file_service->deleteByDb($request, $configs['logo_header'] ?? null);
            $data->logo_header = $this->file_service->upload($request, $folder, $data->logo_header_file);
        }
        if ($data->logo_footer_del || Validator::isUploadedFile($data->logo_footer_file)) {
            $this->file_service->deleteByDb($request, $configs['logo_footer'] ?? null);
            $data->logo_footer = $this->file_service->upload($request, $folder, $data->logo_footer_file);
        }
        // 파일 필드 제거
        unset($data->logo_header_del);
        unset($data->logo_footer_del);

        $this->config_service->upsertConfigs('design', $data->publics());

        return $this->redirectRoute($request, $response, 'admin.design.logo');
    }
}
