<?php

namespace App\Social;

class Provider
{
    protected const PROVIDER_NAME = '';

    protected const KEYS = [
        'id' => 'Client ID',
        'secret' => 'Client Secret'
    ];

    public static function getProviderName(): string
    {
        return static::PROVIDER_NAME ?? '';
    }

    public static function getKeys(): array
    {
        return static::KEYS;
    }

    public static function getKeyName(string $key): string
    {
        return static::KEYS[$key] ?? '';
    }
}
