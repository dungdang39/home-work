<?php

namespace App\Base\Controller\Admin;

use App\Base\Model\Admin\QaSearchRequest;
use App\Base\Service\QaConfigService;
use App\Base\Service\QaService;
use Core\BaseController;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

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
    public function index(Request $request, Response $response, QaSearchRequest $search_request): Response
    {
        // 검색 조건 설정
        $search_params = $search_request->publics();

        // 검색 데이터 수 조회 및 페이징 정보 설정
        $total_count = $this->service->fetchQasTotalCount($search_params);
        $search_request->setTotalCount($total_count);

        // Q&A 목록 조회
        $qas = $this->service->getQas($search_params);
        // 카테고리 목록 조회
        $categories = $this->config_service->getCategories();

        $response_data = [
            'categories' => $categories,
            'qas' => $qas,
            'total_count' => $total_count,
            'search' => $search_request,
            'pagination' => $search_request->getPaginationInfo(),
            'query_params' => $request->getQueryParams(),
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/member/qa/list.html', $response_data);
    }

    /**
     * Q&A 상세 폼 페이지
     */
    public function view(Request $request, Response $response, int $id): Response
    {
        $qa = $this->service->getQa($id);
        $response_data = [
            'qa' => $qa,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/member/qa/form.html', $response_data);
    }

    /**
     * Q&A 수정
     */
    public function update(Request $request, Response $response, int $id): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $redirect_url = $routeContext->getRouteParser()->urlFor('admin.dashboard');
        return $response->withHeader('Location', $redirect_url)->withStatus(302);
    }

    /**
     * Q&A 삭제
     */
    public function delete(Request $request, Response $response, int $id): Response
    {
        $qa = $this->service->getQa($id);

        $this->service->deleteQa($qa['id']);

        return $response->withJson(['message' => '삭제되었습니다.']);
    }
}
