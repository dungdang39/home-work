<?php

namespace App\Admin\Model;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
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

    public function __construct(Request $request, Validator $validator)
    {
        $this->initializeFromRequest($request, $validator);

        $this->checkInterceptIp($this->cf_intercept_ip);

        $this->filterProperties();
    }

    /**
     * 태그를 허용하는 속성을 제외한 모든 속성 필터링
     * * @todo trait로 분리 예정
     * @return void
     */
    protected function filterProperties(): void
    {
        $allow_tags = ['cf_add_script', 'cf_add_css', 'cf_add_meta'];

        foreach ($this as $key => $value) {
            if (is_string($value) && !in_array($key, $allow_tags)) {
                $this->$key = strip_tags(clean_xss_attributes($value));
            }
        }
    }

    /**
     * 차단 IP 체크
     * @param string $cf_intercept_ip  차단 IP
     * @throws \Exception
     * @return void
     */
    protected function checkInterceptIp(string $cf_intercept_ip): void
    {
        if (!empty($cf_intercept_ip)) {
            $remote_addr = $_SERVER['REMOTE_ADDR'];
            $patterns = explode("\n", trim($cf_intercept_ip));
            foreach ($patterns as $pattern) {
                $pattern = trim($pattern);
                if (empty($pattern)) {
                    continue;
                }

                $pattern = str_replace(".", "\.", $pattern);
                $pattern = str_replace("+", "[0-9\.]+", $pattern);
                $pat = "/^{$pattern}$/";

                if (preg_match($pat, $remote_addr)) {
                    $this->throwException("현재 접속 IP : " . $remote_addr . " 가 차단될 수 있기 때문에, 다른 IP를 입력해 주세요.");
                }
            }
        }
    }
}