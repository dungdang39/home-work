<?php

namespace App\Social\Provider;

use App\Social\Provider;

class Google extends Provider
{
    public const PROVIDER_NAME = '구글';
    public const KEYS = [
        'id' => 'Client ID', 
        'secret' => 'Client Secret'
    ];
}