<?php

namespace Core\Image\Strategies;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class ImageStrategyV2 implements ImageStrategyInterface {
    private $imageManager;

    public function __construct() {
        $this->imageManager = new ImageManager();
    }

    public function readImage($data): Image {
        return $this->imageManager->make($data);
    }
}