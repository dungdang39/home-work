<?php

namespace App\Base\Controller\Admin;

use App\Base\Model\Admin\UpdateConfigRequest;
use App\Base\Service\ConfigService;
use App\Base\Service\MemberService;
use Core\BaseController;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class ConfigController extends BaseController
{
    private ConfigService $service;
    private MemberService $member_service;

    public function __construct(
        ConfigService $service,
        MemberService $member_service
    ) {
        $this->service = $service;
        $this->member_service = $member_service;
    }

    /**
     * 기본환경 설정 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $configs = $this->service->getConfigs('config');
        $admins = $this->member_service->fetchMemberByLevel(10);

        $response_data = [
            'configs' => $configs,
            'admins' => $admins,
            'current_ip' => getRealIp($request)
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/config/form.html', $response_data);
    }

    /**
     * 기본환경 설정 업데이트
     */
    public function update(Request $request, Response $response, UpdateConfigRequest $request_data): Response
    {
        $datas = $request_data->publics();
        $configs = $this->service->getConfigs('config');

        foreach ($datas as $name => $value) {
            if (is_null($value)) {
                continue;
            }
            if (array_key_exists($name, $configs)) {
                $this->service->update('config', $name, $value);
            } else {
                $this->service->insert([
                    'scope' => 'config',
                    'name' => $name,
                    'value' => $value,
                ]);
            }
        }

        return $this->redirectRoute($request, $response, 'admin.config.basic');
    }
}