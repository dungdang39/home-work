<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;
use Slim\Psr7\UploadedFile;

class UpdateSocialProviderRequest
{
    use SchemaHelperTrait;

    /**
     * @var array
     */
    public array $socials = [];

    public ?UploadedFile $key_file = null;

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }

    protected function validate()
    {
        if ($this->key_file && !Validator::validExtension($this->key_file, ['p8'])) {
            $this->throwException('키 파일은 p8 확장자만 업로드 가능합니다.');
        }
    }

    protected function afterValidate()
    {
        $this->ensureDefaults();
    }

    /**
     * Ensure default values for the properties.
     */
    private function ensureDefaults()
    {
        foreach ($this->socials as &$data) {
            if (!isset($data['is_enabled'])) {
                $data['is_enabled'] = 0;
            } else {
                $data['is_enabled'] = (int)$data['is_enabled'];
            }
            if (!isset($data['is_test'])) {
                $data['is_test'] = 0;
            } else {
                $data['is_test'] = (int)$data['is_test'];
            }
        }
    }
}
