<?php

namespace App\Base\Social\Provider\Config;

use App\Base\Social\ProviderConfig;
use Hybridauth\Provider\Apple;
use Slim\Http\ServerRequest as Request;

class AppleConfig extends ProviderConfig
{
    public const PROVIDER = 'apple';
    protected const PROVIDER_NAME = '애플';
    protected const KEYS = [
        'id' => 'ID', 
        'team_id' => 'Team ID',
        "key_id" => "Key ID",
        "key_content" => "Key",
        "key_file" => "Key file path"
    ];

    public static function getConfig(Request $request, array $social): array
    {
        return [
            'providers' => [
                'Apple' => [
                    'enabled' => $social['is_enabled'] ? true : false,
                    'adapter' => Apple::class,
                    'callback' => self::getCallbackUrl($request, $social['provider']),
                    'supportRequestState' => false,
                    'keys' => $social['keys'],
                ],
            ]
        ];
    }
}