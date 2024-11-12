<?php

namespace App\Base\Controller\Admin;

use App\Base\Model\Admin\QaConfigRequest;
use App\Base\Model\Admin\UpdateQaCategoryReqeust;
use App\Base\Service\ConfigService;
use App\Base\Service\NotificationService;
use App\Base\Service\QaConfigService;
use Core\BaseController;
use Core\Lib\FlashMessage;
use DI\Container;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class QaConfigController extends BaseController
{
    private ConfigService $config_service;
    private FlashMessage $flash;
    private NotificationService $noti_service;
    private QaConfigService $service;

    public function __construct(
        Container $container,
        ConfigService $config_service,
        NotificationService $noti_service,
        QaConfigService $service
    ) {
        $this->config_service = $config_service;
        $this->noti_service = $noti_service;
        $this->service = $service;
        $this->flash = $container->get('flash');
    }

    /**
     * Q&A 환경설정 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        // Q&A 환경설정 조회
        $qa_config = $this->config_service->getConfigs('qa');
        // Q&A 카테고리 조회
        $qa_categories = $this->service->getCategories();

        $alimtalk_enabled = (bool)$this->noti_service->getNotificationByType('alimtalk')['is_enabled'];
        $sms_enabled = (bool)$this->noti_service->getNotificationByType('sms')['is_enabled'];

        $response_data = [
            "qa_config" => $qa_config,
            "qa_categories" => $qa_categories,
            "alimtalk_enabled" => $alimtalk_enabled,
            "sms_enabled" => $sms_enabled,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/member/qa/config_form.html', $response_data);
    }

    /**
     * Q&A 환경설정 수정
     */
    public function update(Request $request, Response $response, QaConfigRequest $data): Response
    {
        $this->config_service->upsertConfigs('qa', $data->publics());

        return $this->redirectRoute($request, $response, 'admin.member.qa.config');
    }

    /**
     * Q&A 카테고리 서식 조회
     */
    public function getCategory(Request $request, Response $response, int $id): Response
    {
        $category = $this->service->getCategory($id);

        return $response->withJson(['template' => $category['template']], 200);
    }

    /**
     * Q&A 카테고리 추가
     */
    public function createCategory(Request $request, Response $response): Response
    {
        $title = $request->getParsedBody()['category_title'] ?? null;

        $this->service->createCategory(['title' => $title]);

        return $this->redirectRoute($request, $response, 'admin.member.qa.config');
    }

    /**
     * Q&A 카테고리 기본서식 추가/수정
     */
    public function updateBasicTemplate(Request $request, Response $response): Response
    {
        $category_basic_template = $request->getParsedBody()['category_basic_template'] ?? null;

        $this->config_service->upsertConfigs('qa', ['category_basic_template' => $category_basic_template]);

        $this->flash->setMessage('저장되었습니다.');

        return $this->redirectRoute($request, $response, 'admin.member.qa.config');
    }

    /**
     * Q&A 카테고리 별 서식 수정
     */
    public function updateCategory(Request $request, Response $response, int $id, UpdateQaCategoryReqeust $data): Response
    {
        $category = $this->service->getCategory($id);

        $this->service->updateCategory($category['id'], $data->publics());

        return $response->withJson(['message' => '저장되었습니다.'], 200);
    }

    /**
     * Q&A 카테고리 삭제
     */
    public function deleteCategory(Request $request, Response $response, int $id): Response
    {
        $category = $this->service->getCategory($id);

        $this->service->deleteCategory($category['id']);

        return $response->withJson(['message' => '삭제되었습니다.'], 200);
    }
}
