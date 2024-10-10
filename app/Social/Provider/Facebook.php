<?php

namespace App\Social\Provider;

use App\Social\Provider;

class Facebook extends Provider
{
    public const PROVIDER_NAME = '페이스북';
    public const KEYS = [
        'id' => 'Application ID',
        'secret' => 'application Secret'
    ];
}