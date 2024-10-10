<?php

namespace App\Social\Provider;

use App\Social\Provider;

class Apple extends Provider
{
    public const PROVIDER_NAME = '애플';
    public const KEYS = [
        'id' => 'ID', 
        'team_id' => 'Team ID',
        "key_id" => "Key ID",
        "key_content" => "Key",
        "key_file" => "Key file path"
    ];
}