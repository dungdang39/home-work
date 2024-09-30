<?php

namespace App\Admin\Controller;

use App\Admin\Model\UpdateConfigRequest;
use App\Config\ConfigService;
use App\Member\MemberService;
use Core\BaseController;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class MainPageController extends BaseController
{
    private ConfigService $service;

    public function __construct(
        ConfigService $service,
    ) {
        $this->service = $service;
    }

    /**
     * 기본환경 설정 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $response_data = [
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/mainpage_form.html', $response_data);
    }

    /**
     * 기본환경 설정 업데이트
     */
    public function update(Request $request, Response $response, UpdateConfigRequest $data): Response
    {
        $this->service->update($data->publics());

        return $this->redirectRoute($request, $response, 'admin.design.mainpage');
    }
}
