<?php

namespace App\Social\Provider\Config;

use App\Social\Provider\Kakao;
use App\Social\ProviderConfig;
use Slim\Http\ServerRequest as Request;

class KakaoConfig extends ProviderConfig
{
    protected const PROVIDER = 'kakao';
    protected const PROVIDER_NAME = '카카오';
    protected const KEYS = [
        'id' => 'REST API Key', 
        'secret' => 'Client Secret'
    ];

    public static function getConfig(Request $request, array $social): array
    {
        return [
            'callback' => self::getCallbackUrl($request, $social['provider']),
            'providers' => [
                'Kakao' => [
                    'enabled' => $social['is_enabled'] ? true : false,
                    'adapter' => Kakao::class,
                    
                    'supportRequestState' => false,
                    'keys' => $social['keys'],
                ],
            ]
        ];
    }
}