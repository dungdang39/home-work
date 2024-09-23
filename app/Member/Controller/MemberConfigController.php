<?php

namespace App\Member\Controller;

use App\Member\MemberConfigService;
use App\Member\Model\MemberConfigRequest;
use Core\BaseController;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class MemberConfigController extends BaseController
{
    private MemberConfigService $service;

    public function __construct(
        MemberConfigService $service,
    ) {
        $this->service = $service;
    }

    /**
     * 회원 > 기본환경설정 폼 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $member_config = $this->service->getMemberConfig();

        $response_data = [
            "member_config" => $member_config,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/member_config_form.html', $response_data);
    }

    /**
     * 회원 > 기본환경설정 수정
     */
    public function update(Request $request, Response $response, MemberConfigRequest $data): Response
    {
        $member_config = $this->service->getMemberConfig();
        $publics = $data->publics();

        if (empty($member_config)) {
            $this->service->insert($publics);
        } else {
            $this->service->update($publics);
        }

        return $this->redirectRoute($request, $response, 'admin.member.config.basic');
    }
}
