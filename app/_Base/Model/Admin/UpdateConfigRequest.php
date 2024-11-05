<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Sanitizer;
use Core\Validator\Validator;
use Exception;
use Slim\Http\ServerRequest as Request;
use Slim\Psr7\UploadedFile;

/**
 * 환경설정 업데이트 요청 객체
 */
class UpdateConfigRequest
{
    use SchemaHelperTrait;

    public string $site_title = '';
    public string $site_description = '';
    public string $site_keyword = '';
    public ?string $site_image = '';
    public string $super_admin = '';
    public string $privacy_officer_name = '';
    public string $privacy_officer_email = '';
    public ?int $use_mail = 0;
    public ?string $mail_address = '';
    public ?string $mail_name = '';
    public ?string $company_name = '';
    public ?string $biz_reg_no = '';
    public ?string $ceo_name = '';
    public ?string $main_phone_number = '';
    public ?string $fax_number = '';
    public ?string $ecom_reg_no = '';
    public ?string $add_telecom_no = '';
    public ?string $biz_zip_code = '';
    public ?string $biz_address = '';
    public ?string $biz_address_detail = '';
    public ?string $possible_ip = '';
    public ?string $intercept_ip = '';
    public ?string $add_script = '';
    public ?string $add_css = '';
    public ?string $add_meta = '';
    
    public ?UploadedFile $site_image_file;
    public ?int $delete_site_image = 0;

    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->initializeFromRequest($request);
    }

    protected function validate()
    {
        $this->validateRequired('site_title', '사이트 제목');
        $this->validateSiteImage();
        $this->validateRequired('super_admin', '최고관리자');
        $this->validateRequired('privacy_officer_name', '개인정보관리 책임자명');
        $this->validateRequired('privacy_officer_email', '개인정보관리 메일 주소');
        if ($this->use_mail) {
            $this->validateRequired('mail_address', '메일 발송 주소');
            if (!Validator::isValidEmail($this->mail_address)) {
                $this->throwException('메일 발송 주소가 올바르지 않은 형식입니다.');
            }
        }
        if ($this->biz_reg_no) {
            if (!Validator::isMaxLength($this->biz_reg_no, 12)) {
                $this->throwException('사업자등록번호는 12자리 이하로 입력해 주세요.');
            }
        }
        $this->checkInterceptIp($this->intercept_ip);
    }

    protected function afterValidate(): void
    {
        Sanitizer::cleanXssAll($this, ['add_script', 'add_css', 'add_meta']);
        $this->possible_ip = Sanitizer::removeDuplicateLines($this->possible_ip);
        $this->intercept_ip = Sanitizer::removeDuplicateLines($this->intercept_ip);
        $this->biz_zip_code = substr($this->biz_zip_code, 0, 5);
    }

    /**
     * 사이트 대표이미지 검사
     */
    private function validateSiteImage(): void
    {
        if (Validator::isUploadedFile($this->site_image_file)) {
            if (!Validator::isImage($this->site_image_file)) {
                $this->throwException('이미지 파일만 업로드 할 수 있습니다.');
            }
        }
    }

    /**
     * 현재 IP가 차단되는지 검사.
     * @param string $intercept_ip 차단 IP
     * @throws Exception   
     * @return void
     */
    private function checkInterceptIp(?string $intercept_ip = null): void
    {
        if (!empty($intercept_ip)) {
            $remote_addr = getRealIp($this->request);
            $patterns = explode("\n", trim($intercept_ip));

            foreach ($patterns as $pattern) {
                $pattern = trim($pattern);
                if (empty($pattern)) {
                    continue;
                }

                $pattern = str_replace(".", "\.", $pattern);
                $pattern = str_replace("+", "[0-9\.]+", $pattern);
                $pattern = "/^{$pattern}$/";

                if (preg_match($pattern, $remote_addr)) {
                    $this->throwException("현재 접속 IP : " . $remote_addr . " 가 차단될 수 있기 때문에, 다른 IP를 입력해 주세요.");
                }
            }
        }
    }
}