<?php

namespace App\Social\Provider;

use App\Social\Provider;

class Twitter extends Provider
{
    public const PROVIDER_NAME = '트위터';
    protected const KEYS = [
        'key' => 'Consumer Key', 
        'secret' => 'Consumer Secret'
    ];
}