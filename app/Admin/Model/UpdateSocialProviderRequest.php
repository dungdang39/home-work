<?php

namespace App\Admin\Model;

use Core\Traits\SchemaHelperTrait;
use Slim\Http\ServerRequest as Request;

class UpdateSocialProviderRequest
{
    use SchemaHelperTrait;

    /**
     * @var array
     */
    public array $providers = [];

    /**
     * SocialUpdateRequest constructor.
     */
    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
        
    }

    protected function validate()
    {
        $this->ensureDefaults();
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
}
