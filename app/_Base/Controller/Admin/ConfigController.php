<?php

namespace App\Base\Controller\Admin;

use App\Base\Model\Admin\UpdateConfigRequest;
use App\Base\Service\ConfigService;
use App\Base\Service\MemberService;
use Core\BaseController;
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
    private FileService $file_service;
    private MailService $mail_service;
    private MemberService $member_service;

    public function __construct(
        ConfigService $service,
        FileService $file_service,
        MailService $mail_service,
        MemberService $member_service
    ) {
        $this->service = $service;
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

        $response_data = [
            'configs' => $configs,
            'admins' => $admins,
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
     */
    public function sendMailTest(Request $request, Response $response): Response
    {
        $mail = $request->getParam('mail_address');
        if (empty($mail)) {
            throw new HttpBadRequestException($request, '메일 발송 주소를 입력해주세요.');
        }
        if (!Validator::isValidEmail($mail)) {
            throw new HttpBadRequestException($request, '메일 발송 주소가 올바르지 않은 형식입니다.');
        }

        // 테스트 메일 발송
        $content = Twig::fromRequest($request)->fetch('@mail/test.html');
        $this->mail_service->setUseMail(true);
        $option = ['from_mail' => $mail];
        $result = $this->mail_service->send($mail, '테스트 메일 발송', $content, $option);
        if (!$result) {
            throw new HttpBadRequestException($request, '메일 발송에 실패하였습니다.');
        }
        return $response->withJson(['message' => $mail . '메일로 테스트 메일을 발송하였습니다.']);
    }
}
