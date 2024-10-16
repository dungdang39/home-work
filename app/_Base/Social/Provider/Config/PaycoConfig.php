<?php

namespace App\Base\Social\Provider\Config;

use App\Base\Social\ProviderConfig;
use Slim\Http\ServerRequest as Request;

/**
 * @todo Implement PaycoConfig
 */
class PaycoConfig extends ProviderConfig
{
    protected const PROVIDER = 'payco';
    protected const PROVIDER_NAME = '페이코';
    protected const KEYS = [
        'id' => 'Client ID',
        'secret' => 'Secret'
    ];

    public static function getConfig(Request $request, array $social): array
    {
        return [
            'providers' => [
                'Naver' => [
                    'enabled' => $social['is_enabled'] ? true : false,
                    // 'adapter' => Payco::class,
                    'callback' => self::getCallbackUrl($request, $social['provider']),
                    'supportRequestState' => false,
                    'keys' => $social['keys'],
                ],
            ]
        ];
    }
}