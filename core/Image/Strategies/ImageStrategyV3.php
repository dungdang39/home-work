<?php

namespace Core\Image\Strategies;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class ImageStrategyV3 implements ImageStrategyInterface {
    private $imageManager;

    public function __construct() {
        $this->imageManager = new ImageManager(new Driver());
    }

    public function readImage($data): Image {
        return $this->imageManager->read($data);
    }
}