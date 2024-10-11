<?php

namespace App\Social\Provider\Config;

use App\Social\ProviderConfig;
use Hybridauth\Provider\Facebook;
use Slim\Http\ServerRequest as Request;

class FacebookConfig extends ProviderConfig
{
    protected const PROVIDER = 'facebook';
    protected const PROVIDER_NAME = '페이스북';
    protected const KEYS = [
        'id' => 'Application ID',
        'secret' => 'application Secret'
    ];

    public static function getConfig(Request $request, array $social): array
    {
        return [
            'providers' => [
                'Facebook' => [
                    'enabled' => $social['is_enabled'] ? true : false,
                    'adapter' => Facebook::class,
                    'callback' => self::getCallbackUrl($request, $social['provider']),
                    'supportRequestState' => false,
                    'keys' => $social['keys'],
                ],
            ]
        ];
    }
}