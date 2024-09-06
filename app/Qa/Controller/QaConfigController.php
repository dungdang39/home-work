<?php

namespace App\Qa\Controller;

use App\Qa\Model\QaConfigRequest;
use App\Qa\Service\QaConfigService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

class QaConfigController
{
    private QaConfigService $service;
    private QaConfigRequest $request_model;

    public function __construct(
        QaConfigService $service,
        QaConfigRequest $request_model
    ) {
        $this->service = $service;
        $this->request_model = $request_model;
    }

    /**
     * Q&A 목록 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        // Q&A 설정 조회
        $qa_config = $this->service->getQaConfig();

        $response_data = [
            "qa_config" => $qa_config,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/qa_config_form.html', $response_data);
    }

    /**
     * Q&A 수정
     */
    public function update(Request $request, Response $response): Response
    {
        try {
            // Q&A 설정 조회
            $qa_config = $this->service->getQaConfig();
            $request_body = $request->getParsedBody();
            $data = $this->request_model->load($request_body);

            
            if ($qa_config) {
                $this->service->update($data->toArray());
            } else {
                $this->service->insert($data->toArray());
            }

            $routeContext = RouteContext::fromRequest($request);
            $redirect_url = $routeContext->getRouteParser()->urlFor('admin.member.qa.config');
            return $response->withHeader('Location', $redirect_url)->withStatus(302);

        } catch (\Exception $e) {
            return api_response_json($response, [
                'result' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}
