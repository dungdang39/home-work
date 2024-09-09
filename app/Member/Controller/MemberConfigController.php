<?php

namespace App\Member\Controller;

use App\Member\MemberConfigService;
use App\Member\Model\UpdateMemberConfigRequest;
use Core\BaseController;
use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class MemberConfigController extends BaseController
{
    private MemberConfigService $service;

    public function __construct(
        Container $container,
        MemberConfigService $service,
    ) {
        parent::__construct($container);

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
    public function update(Request $request, Response $response): Response
    {
        try {
            $request_body = $request->getParsedBody();
            $data = new UpdateMemberConfigRequest($request_body);

            $member_config = $this->service->getMemberConfig();

            if (empty($member_config)) {
                $this->service->insert($data->toArray());
            } else {
                $this->service->update($data->toArray());
            }
        } catch (\Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        return $this->redirectRoute($request, $response, 'admin.member.config');
    }
}
