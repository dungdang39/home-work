<?php

namespace App\Base\Controller\Admin;

use App\Base\Service\QaConfigService;
use App\Base\Service\QaService;
use Core\BaseController;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;
use Exception;

class QaController extends BaseController
{
    private QaService $service;
    private QaConfigService $config_service;

    public function __construct(
        QaService $service,
        QaConfigService $config_service
    ) {
        $this->service = $service;
        $this->config_service = $config_service;
    }

    /**
     * Q&A 목록 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $response_data = [
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/member_list.html', $response_data);
    }

    /**
     * Q&A 등록 폼 페이지
     */
    public function create(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/member_form.html');
    }

    /**
     * Q&A 등록
     */
    public function insert(Request $request, Response $response): Response
    {
        return $this->redirectRoute($request, $response, 'admin.member.qa');
    }

    /**
     * Q&A 상세 폼 페이지
     */
    public function view(Request $request, Response $response, $qa_id): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/member_form.html');
    }

    /**
     * Q&A 수정
     */
    public function update(Request $request, Response $response, array $qa_id): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $redirect_url = $routeContext->getRouteParser()->urlFor('admin.dashboard');
        return $response->withHeader('Location', $redirect_url)->withStatus(302);
    }

    /**
     * Q&A 삭제
     * - 실제 삭제하지 않고 탈퇴일자 및 Q&A메모를 업데이트한다.
     */
    public function delete(Request $request, Response $response, array $qa_id): Response
    {
        return $this->redirectRoute($request, $response, 'admin.dashboard');
    }
}
