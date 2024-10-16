<?php

namespace App\Base\Social\Provider\Config;

use App\Base\Social\ProviderConfig;
use Hybridauth\Provider\Twitter;
use Slim\Http\ServerRequest as Request;

class TwitterConfig extends ProviderConfig
{
    protected const PROVIDER = 'twitter';
    protected const PROVIDER_NAME = '트위터';
    protected const KEYS = [
        'key' => 'Consumer Key', 
        'secret' => 'Consumer Secret'
    ];

    public static function getConfig(Request $request, array $social): array
    {
        return [
            'providers' => [
                'Twitter' => [
                    'enabled' => $social['is_enabled'] ? true : false,
                    'adapter' => Twitter::class,
                    'callback' => self::getCallbackUrl($request, $social['provider']),
                    'supportRequestState' => false,
                    'keys' => $social['keys'],
                ],
            ]
        ];
    }
}