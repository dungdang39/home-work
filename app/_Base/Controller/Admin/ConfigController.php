<?php

namespace App\Base\Controller\Admin;

use App\Base\Model\Admin\SendMailTestRequest;
use App\Base\Model\Admin\UpdateConfigRequest;
use App\Base\Service\ConfigService;
use App\Base\Service\MemberService;
use Core\BaseController;
use Core\EditorService;
use Core\Exception\HttpUnprocessableEntityException;
use Core\FileService;
use Core\MailService;
use Core\Validator\Validator;
use Slim\Exception\HttpBadRequestException;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

/**
 * 기본환경 설정 컨트롤러
 */
class ConfigController extends BaseController
{
    private ConfigService $service;
    private EditorService $editor_service;
    private FileService $file_service;
    private MailService $mail_service;
    private MemberService $member_service;

    public function __construct(
        ConfigService $service,
        EditorService $editor_service,
        FileService $file_service,
        MailService $mail_service,
        MemberService $member_service
    ) {
        $this->service = $service;
        $this->editor_service = $editor_service;
        $this->file_service = $file_service;
        $this->mail_service = $mail_service;
        $this->member_service = $member_service;
    }

    /**
     * 기본환경 설정 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        $configs = $this->service->getConfigs('config');
        $admins = $this->member_service->fetchMemberByLevel(10);
        $editors = $this->editor_service->getEditorsByPath();

        $response_data = [
            'configs' => $configs,
            'admins' => $admins,
            'editors' => $editors,
            'current_ip' => getRealIp($request)
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/config/form.html', $response_data);
    }

    /**
     * 기본환경 설정 업데이트
     * @todo 이미지 처리 부분을 더 간결하게 사용할 수 있도록 개선이 필요함.
     */
    public function update(Request $request, Response $response, UpdateConfigRequest $data): Response
    {
        if ($data->delete_site_image || Validator::isUploadedFile($data->site_image_file)) {
            $site_image = $this->service->getConfig('config', 'site_image');
            $this->file_service->deleteByDb($request, $site_image);
            $data->site_image = $this->file_service->upload($request, 'config', $data->site_image_file);
        }
        unset($data->delete_site_image);

        $this->service->upsertConfigs('config', $data->publics());

        return $this->redirectRoute($request, $response, 'admin.config.basic');
    }

    /**
     * 테스트 메일 발송
     * @throws HttpBadRequestException
     * @throws HttpUnprocessableEntityException
     */
    public function sendMailTest(Request $request, Response $response, SendMailTestRequest $data): Response
    {
        $mail = $data->mail_address;
        $mail_name = $data->mail_name;
        $test_mail_addresses = $data->test_mail_addresses;
        $success = [];
        $fail = [];

        $content = Twig::fromRequest($request)->fetch('@mail/test.html');
        $option = ['from_mail' => $mail, 'from_name' => $mail_name];

        foreach ($test_mail_addresses as $test_mail) {
            $result = $this->mail_service->send($test_mail, '테스트 메일 발송', $content, $option);
            if ($result) {
                $success[] = $test_mail;
            } else {
                $fail[] = $test_mail;
            }
        }

        $message = number_format(count($success)) . '건의 테스트 이메일 발송에 성공했어요.';
        if (count($fail) > 0) {
            $message .= ' (실패 : ' . number_format(count($fail)) . '건)';
        }
        return $response->withJson(['message' => $message]);
    }
}
