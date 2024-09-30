<?php

namespace Extend\Container;

use ContainerInterface;
use DI\Container;

class Example implements ContainerInterface
{
    private const ENABLE = true;

    public static function load(Container $container) {}

    public static function isEnable()
    {
        return self::ENABLE;
    }
}
