<?php

namespace App\Admin\Model;

use Core\Traits\SchemaHelperTrait;

class CreateSocialProviderRequest
{
    use SchemaHelperTrait;

    public string $provider_name;
    public string $provider_key;
    public ?string $client_id = '';
    public ?string $client_secret = '';

    public function __construct(array $data)
    {
        $this->mapDataToProperties($this, $data);
        $this->validate();
    }

    public static function load(array $data): self
    {
        return new self($data);
    }

    private function validate()
    {
        if (empty($this->provider_name)) {
            $this->throwException('제공자명을 입력해주세요.');
        }
        if (empty($this->provider_key)) {
            $this->throwException('제공자키를 입력해주세요.');
        }
    }
}