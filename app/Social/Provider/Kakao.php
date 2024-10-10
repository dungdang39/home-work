<?php

namespace App\Social\Provider;

use App\Social\Provider;

class Kakao extends Provider
{
    public const PROVIDER_NAME = '카카오';
    protected const KEYS = [
        'id' => 'REST API Key', 
        'secret' => 'Client Secret'
    ];
}