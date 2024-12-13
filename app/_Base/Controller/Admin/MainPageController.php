<?php

namespace App\Base\Controller\Admin;

use App\Base\Model\Admin\CreateMainpageRequest;
use App\Base\Model\Admin\UpdateMainpageListRequest;
use App\Base\Model\Admin\UpdateMainpageRequest;
use App\Base\Service\MainPageService;
use App\Base\Service\ConfigService;
use Core\BaseController;
use Core\Exception\HttpConflictException;
use Core\Lib\FlashMessage;
use DI\Container;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class MainPageController extends BaseController
{
    private FlashMessage $flash;
    private ConfigService $config_service;
    private MainPageService $service;

    public function __construct(
        Container $container,
        ConfigService $config_service,
        MainPageService $service,
    ) {
        $this->flash = $container->get('flash');
        $this->config_service = $config_service;
        $this->service = $service;
    }

    /**
     * 메인화면 설정 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $sections = $this->service->getSections();

        $response_data = [
            'sections' => $sections,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/design/mainpage/form.html', $response_data);
    }

    /**
     * 메인화면 설정 업데이트
     */
    public function insert(Request $request, Response $response, CreateMainpageRequest $data): Response
    {
        if ($this->service->exists($data->section, $data->section_title)) {
            throw new HttpConflictException($request, '이미 존재하는 섹션입니다.');
        }

        $this->service->insert($data->publics());

        $this->flash->setMessage('저장되었습니다.');
        return $this->redirectRoute($request, $response, 'admin.design.mainpage');
    }

    /**
     * 메인화면 설정 1건 조회
     */
    public function get(Response $response, string $id): Response
    {
        $section = $this->service->getSection($id);

        return $response->withJson([
            'message' => "메인페이지 섹션 조회가 완료되었습니다.",
            'row' => $section
        ], 200);
    }

    /**
     * 메인화면 설정 업데이트
     */
    public function update(Request $request, Response $response, int $id, UpdateMainpageRequest $data): Response
    {
        $section_info = $this->service->getSection($id);

        if ($this->service->exists($data->section, $data->section_title, $id)) {
            throw new HttpConflictException($request, '이미 존재하는 섹션입니다.');
        }
        if ($section_info['section'] === 'banner') {
            $count = $this->service->getDataCountBySection($section_info);

            if (
                $data->section_title != $section_info['section_title']
                && $count > 0 
            ) {
                throw new HttpConflictException($request, '배너가 등록된 섹션은 타이틀을 변경할 수 없습니다.');
            }
        }

        $this->service->update($id, $data->publics());

        $this->flash->setMessage('저장되었습니다.');
        return $this->redirectRoute($request, $response, 'admin.design.mainpage');
    }

    /**
     * 메인화면 목록 일괄 업데이트
     */
    public function updateList(Request $request, Response $response, UpdateMainpageListRequest $data): Response
    {
        $errors = [];
        foreach ($data->sections as $id => $list_data) {
            $section_info = $this->service->fetch($id);
            if (!$section_info) {
                $errors[] = "{$id} : 섹션정보가 존재하지 않습니다.";
                continue;
            }
            if ($this->service->exists($section_info['section'], $list_data['section_title'], $id)) {
                $errors[] = "{$id} : 이미 존재하는 섹션입니다.";
                continue;
            }
            if ($section_info['section'] === 'banner') {
                $count = $this->service->getDataCountBySection($section_info);

                if (
                    $list_data['section_title'] != $section_info['section_title']
                    && $count > 0 
                ) {
                    $errors[] = "{$id} : 배너가 등록된 섹션은 타이틀을 변경할 수 없습니다.";
                    continue;
                }
            }

            $this->service->update($id, $list_data);
        }

        $this->config_service->update('design', 'use_mainpage', $data->use_mainpage);

        $this->flash->setMessage($errors ?: '저장되었습니다.');
        return $this->redirectRoute($request, $response, 'admin.design.mainpage');
    }

    /**
     * 메인화면 설정 삭제
     */
    public function delete(Request $request, Response $response, int $id): Response
    {
        $section = $this->service->getSection($id);

        if ($this->service->getDataCountBySection($section) > 0) {
            throw new HttpConflictException($request, '섹션에 등록된 데이터가 존재합니다.');
        }

        $this->service->delete($section['id']);

        return $response->withJson(['message' => '섹션이 삭제되었습니다.']);
    }
}
