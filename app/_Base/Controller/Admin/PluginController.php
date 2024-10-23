<?php

namespace App\Base\Controller\Admin;

use App\Base\Model\Admin\SearchPluginRequest;
use Core\BaseController;
use Core\PluginService;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

/**
 * 플러그인 관리
 */
class PluginController extends BaseController
{
    protected PluginService $service;

    public function __construct(
        PluginService $service
    ) {
        $this->service = $service;
    }

    /**
     * 플러그인 관리 목록 페이지
     */
    public function index(Request $request, Response $response, SearchPluginRequest $search_request): Response
    {
        // 검색 조건 설정
        $search_params = $search_request->publics();

        // 총 데이터 수 조회
        $total_count = $this->service->getPluginsTotalCount($search_params);
        $search_request->setTotalCount($total_count);

        $plugins = $this->service->getPlugins($search_params);

        $response_data = [
            'plugins' => $plugins,
            "total_count" => $total_count,
            "search" => $search_request,
            "query_params" => $request->getQueryParams(),
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/config/plugin_list.html', $response_data);
    }

    /**
     * 플러그인 활성화
     */
    public function activate(Request $request, Response $response, string $plugin): Response
    {
        $plugin_data = $this->service->getPlugin($plugin);
        $this->service->activatePlugin($plugin_data['plugin']);

        return $response->withJson([
            'result' => 'success',
            'message' => '플러그인이 활성화되었습니다.',
        ]);
    }

    /**
     * 플러그인 비활성화
     */
    public function deactivate(Request $request, Response $response, string $plugin): Response
    {
        $plugin_data = $this->service->getPlugin($plugin);
        $this->service->deactivatePlugin($plugin_data['plugin']);

        return $response->withJson([
            'result' => 'success',
            'message' => '플러그인이 비활성화되었습니다.',
        ]);
    }

    /**
     * 플러그인 삭제
     */
    public function uninstall(Request $request, Response $response, string $plugin): Response
    {
        $plugin_data = $this->service->getPlugin($plugin);
        $this->service->uninstallPlugin($plugin_data['plugin']);

        return $response->withJson([
            'result' => 'success',
            'message' => '플러그인이 삭제되었습니다.',
        ]);
    }

}
