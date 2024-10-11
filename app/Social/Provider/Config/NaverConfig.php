<?php

namespace App\Social\Provider\Config;

use App\Social\Provider\Naver;
use App\Social\ProviderConfig;
use Slim\Http\ServerRequest as Request;

class NaverConfig extends ProviderConfig
{
    protected const PROVIDER = 'naver';
    protected const PROVIDER_NAME = '네이버';
    protected const KEYS = [
        'id' => 'Client ID', 
        'secret' => 'Client Secret'
    ];

    public static function getConfig(Request $request, array $social): array
    {
        return [
            'providers' => [
                'Naver' => [
                    'enabled' => $social['is_enabled'] ? true : false,
                    'adapter' => Naver::class,
                    'callback' => self::getCallbackUrl($request, $social['provider']),
                    'supportRequestState' => false,
                    'keys' => $social['keys'],
                ],
            ]
        ];
    }
}