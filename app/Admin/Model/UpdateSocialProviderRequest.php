<?php

namespace App\Admin\Model;

use Core\Traits\SchemaHelperTrait;

class UpdateSocialProviderRequest
{
    use SchemaHelperTrait;

    /**
     * @var array
     */
    public array $providers = [];

    /**
     * SocialUpdateRequest constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->mapDataToProperties($this, $data);
        $this->ensureDefaults();
        $this->validate();
    }

    /**
     * Create a new instance of the request.
     *
     * @param array $data
     * @return self
     */
    public static function load(array $data): self
    {
        return new self($data);
    }

    /**
     * Ensure default values for the properties.
     */
    private function ensureDefaults()
    {
        foreach ($this->providers as &$provider) {
            if (!isset($provider['is_enabled'])) {
                $provider['is_enabled'] = 0;
            } else {
                $provider['is_enabled'] = (int)$provider['is_enabled'];
            }
        }
    }


    /**
     * Validate the data.
     *
     * @throws \Exception
     */
    private function validate()
    {

    }
}
