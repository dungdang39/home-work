<?php

namespace Extend\Container;

use DI\Container;

interface ContainerInterface
{
    /**
     * 추가 컨테이너 로드
     */
    public static function load(Container $container);

    /*
     * 추가 컨테이너 활성화 여부
     */
    public static function isEnable();
}
