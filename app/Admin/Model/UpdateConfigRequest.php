<?php

namespace App\Admin\Model;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Sanitizer;
use Exception;
use Slim\Http\ServerRequest as Request;

/**
 * 환경설정 업데이트 요청 객체
 */
class UpdateConfigRequest
{
    use SchemaHelperTrait;

    public string $cf_site_title = '';
    public string $cf_site_description = '';
    public string $cf_site_keyword = '';
    public string $cf_admin = '';
    public string $cf_privacy_officer_name = '';
    public string $cf_privacy_officer_email = '';
    public int $cf_use_shop = 0;
    public int $cf_use_community = 0;
    public string $cf_company_name = '';
    public string $cf_biz_reg_no = '';
    public string $cf_ceo_name = '';
    public string $cf_main_phone_number;
    public ?string $cf_fax_number = null;
    public string $cf_ecom_reg_no = '';
    public ?string $cf_add_telecom_no = null;
    public string $cf_biz_zip_code = '';
    public string $cf_biz_address = '';
    public ?string $cf_biz_address_detail = null;
    public ?string $cf_biz_address_etc = null;
    public string $cf_possible_ip = '';
    public string $cf_intercept_ip = '';
    public string $cf_add_script = '';
    public string $cf_add_css = '';
    public string $cf_add_meta = '';

    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->initializeFromRequest($request);
    }

    protected function validate()
    {
        $this->checkInterceptIp($this->cf_intercept_ip);
    }

    protected function afterValidate(): void
    {
        Sanitizer::cleanXssAll($this, ['cf_add_script', 'cf_add_css', 'cf_add_meta']);
        $this->cf_possible_ip = Sanitizer::removeDuplicateLines($this->cf_possible_ip);
        $this->cf_intercept_ip = Sanitizer::removeDuplicateLines($this->cf_intercept_ip);
    }

    /**
     * 현재 IP가 차단되는지 검사.
     * @param string $cf_intercept_ip 차단 IP
     * @throws Exception   
     * @return void
     */
    private function checkInterceptIp(string $cf_intercept_ip): void
    {
        if (!empty($cf_intercept_ip)) {
            $remote_addr = getRealIp($this->request);
            $patterns = explode("\n", trim($cf_intercept_ip));

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