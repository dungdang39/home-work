<?php

namespace App\Qa\Controller;

use App\Qa\Model\QaConfigRequest;
use App\Qa\Service\QaConfigService;
use Core\BaseController;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class QaConfigController extends BaseController
{
    private QaConfigService $service;

    public function __construct(
        QaConfigService $service,
    ) {
        $this->service = $service;
    }

    /**
     * Q&A 설정 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        // Q&A 설정 조회
        $qa_config = $this->service->getQaConfig();

        $response_data = [
            "qa_config" => $qa_config,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/member/qa/config_form.html', $response_data);
    }

    /**
     * Q&A 수정
     */
    public function update(Request $request, Response $response, QaConfigRequest $data): Response
    {
        // Q&A 설정 조회
        $qa_config = $this->service->getQaConfig();

        if ($qa_config) {
            $this->service->update($data->publics());
        } else {
            $this->service->insert($data->publics());
        }

        return $this->redirectRoute($request, $response, 'admin.member.qa.config');
    }
}
