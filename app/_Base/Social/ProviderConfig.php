<?php

namespace App\Base\Social;

use Slim\Http\ServerRequest as Request;
use Slim\Routing\RouteContext;

class ProviderConfig
{
    protected const PROVIDER = '';

    protected const PROVIDER_NAME = '';

    protected const KEYS = [
        'id' => 'Client ID',
        'secret' => 'Client Secret'
    ];

    public static function getProvider(): string
    {
        return static::PROVIDER;
    }

    public static function getProviderName(): string
    {
        return static::PROVIDER_NAME;
    }

    public static function getKeys(): array
    {
        return static::KEYS;
    }

    public static function getKeyName(string $key): string
    {
        return static::KEYS[$key] ?? '';
    }

    protected static function getCallbackUrl(Request $request, string $provider): string
    {
        $routeContext = RouteContext::fromRequest($request);
        return $routeContext->getRouteParser()->fullUrlFor(
            $request->getUri(),
            'login.social.callback',
            ['provider' => $provider]
        );
    }
}
