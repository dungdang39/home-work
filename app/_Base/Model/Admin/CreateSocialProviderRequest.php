<?php

namespace App\Base\Model\Admin;

use App\Base\Social\Provider\Config\AppleConfig;
use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;
use Slim\Psr7\UploadedFile;

class CreateSocialProviderRequest
{
    use SchemaHelperTrait;

    public ?string $provider = '';
    public ?int $is_test = 0;
    public array $keys = [];

    public ?UploadedFile $key_file = null;

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }

    protected function validate()
    {
        if (!Validator::required($this->provider)) {
            $this->throwException('서비스 제공자를 입력해주세요.');
        }
        if (AppleConfig::PROVIDER === $this->provider) {
            if ($this->key_file && !Validator::validExtension($this->key_file, ['p8'])) {
                $this->throwException('키 파일은 p8 확장자만 업로드 가능합니다.');
            }
        }
    }
}