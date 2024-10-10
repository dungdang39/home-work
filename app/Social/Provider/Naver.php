<?php

namespace App\Social\Provider;

use App\Social\Provider;

class Naver extends Provider
{
    public const PROVIDER_NAME = '네이버';
    protected const KEYS = [
        'id' => 'Client ID', 
        'secret' => 'Client Secret'
    ];
}