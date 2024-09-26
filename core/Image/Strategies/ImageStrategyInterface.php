<?php

namespace Core\Image\Strategies;

use Intervention\Image\Image;

interface ImageStrategyInterface {

    /**
     * 이미지를 읽어들입니다.
     * 
     * @param mixed $data 이미지 데이터
     * @return \Intervention\Image\Image
     */
    public function readImage($path): Image;
}