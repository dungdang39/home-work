<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Sanitizer;
use Core\Validator\Validator;
use Exception;
use Slim\Http\ServerRequest as Request;

/**
 * 환경설정 업데이트 요청 객체
 */
class UpdateConfigRequest
{
    use SchemaHelperTrait;

    public string $site_title = '';
    public string $site_description = '';
    public string $site_keyword = '';
    public string $super_admin = '';
    public string $privacy_officer_name = '';
    public string $privacy_officer_email = '';
    public ?int $use_shop = 0;
    public ?int $use_community = 0;
    public ?string $company_name = null;
    public ?string $biz_reg_no = null;
    public ?string $ceo_name = null;
    public ?string $main_phone_number = null;
    public ?string $fax_number = null;
    public ?string $ecom_reg_no = null;
    public ?string $add_telecom_no = null;
    public ?string $biz_zip_code = null;
    public ?string $biz_address = null;
    public ?string $biz_address_detail = null;
    public ?string $biz_address_etc = null;
    public ?string $possible_ip = null;
    public ?string $intercept_ip = null;
    public ?string $add_script = null;
    public ?string $add_css = null;
    public ?string $add_meta = null;

    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->initializeFromRequest($request);
    }

    protected function validate()
    {
        $this->validateRequired('site_title', '사이트 제목');
        $this->validateRequired('super_admin', '최고관리자');
        $this->validateRequired('privacy_officer_name', '개인정보관리 책임자명');
        $this->validateRequired('privacy_officer_email', '개인정보관리 메일 주소');
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
     * 필수 입력 사항 검사
     */
    private function validateRequired(string $field, string $label): void
    {
        if (!Validator::required($this->$field)) {
            $this->throwException("{$label}은(는) 필수 입력 사항입니다.");
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