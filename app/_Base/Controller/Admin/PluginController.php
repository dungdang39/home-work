<?php

namespace App\Base\Controller\Admin;

use App\Base\Model\Admin\InstallPluginRequest;
use App\Base\Model\Admin\SearchPluginRequest;
use Core\BaseController;
use Core\Lib\FlashMessage;
use Core\PluginService;
use DI\Container;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

/**
 * 플러그인 관리
 */
class PluginController extends BaseController
{
    protected FlashMessage $flash;
    protected PluginService $service;

    public function __construct(
        Container $container,
        PluginService $service
    ) {
        $this->flash = $container->get('flash');
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
            'message' => '플러그인 사용이 중지되었습니다.',
        ]);
    }

    /**
     * 플러그인 설치
     */
    public function install(Request $request, Response $response, InstallPluginRequest $request_data): Response
    {
        $uploaded_file = $request_data->plugin_file;

        $this->service->extractPlugin($uploaded_file);

        $plugin_folder_name = pathinfo($uploaded_file->getClientFilename(), PATHINFO_FILENAME);
        $plugin_folder_path = $this->service::PLUGIN_DIR . '/' . $plugin_folder_name;
        
        $this->service->checkRequiredFiles($plugin_folder_path);
        
        $this->flash->setMessage('플러그인이 설치되었습니다.');

        return $this->redirectRoute($request, $response, 'admin.config.plugin');
    }

    /**
     * 플러그인 삭제
     */
    public function uninstall(Request $request, Response $response, string $plugin): Response
    {
        $plugin_data = $this->service->getPlugin($plugin);
        $this->service->deactivatePlugin($plugin_data['plugin']);
        $this->service->removePlugin(join(DIRECTORY_SEPARATOR, [$this->service::PLUGIN_DIR, $plugin_data['plugin']]));

        return $response->withJson([
            'result' => 'success',
            'message' => '플러그인이 삭제되었습니다.',
        ]);
    }

}
