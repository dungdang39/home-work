<?php

namespace App\Social\Model;

use Core\Traits\SchemaHelperTrait;
use Slim\Http\ServerRequest as Request;

class UpdateSocialProviderRequest
{
    use SchemaHelperTrait;

    /**
     * @var array
     */
    public array $socials = [];

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
        foreach ($this->socials as &$data) {
            if (!isset($data['is_enabled'])) {
                $data['is_enabled'] = 0;
            } else {
                $data['is_enabled'] = (int)$data['is_enabled'];
            }
        }
    }
}
