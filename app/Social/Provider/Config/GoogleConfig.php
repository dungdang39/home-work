<?php

namespace App\Social\Provider\Config;

use App\Social\ProviderConfig;
use Hybridauth\Provider\Google;
use Slim\Http\ServerRequest as Request;

class GoogleConfig extends ProviderConfig
{
    protected const PROVIDER = 'google';
    protected const PROVIDER_NAME = '구글';
    protected const KEYS = [
        'id' => 'Client ID', 
        'secret' => 'Client Secret'
    ];

    public static function getConfig(Request $request, array $social): array
    {
        return [
            'providers' => [
                'Google' => [
                    'enabled' => $social['is_enabled'] ? true : false,
                    'adapter' => Google::class,
                    'callback' => self::getCallbackUrl($request, $social['provider']),
                    'supportRequestState' => false,
                    'keys' => $social['keys'],
                ],
            ]
        ];
    }
}