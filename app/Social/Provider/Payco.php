<?php

namespace App\Social\Provider;

use App\Social\Provider;

class Payco extends Provider
{
    protected const PROVIDER_NAME = '페이코';
    protected const KEYS = [
        'id' => 'Client ID',
        'secret' => 'Secret'
    ];
}