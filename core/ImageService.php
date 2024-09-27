<?php

namespace Core;

use Core\Image\Strategies\ImageStrategyInterface;
use Core\Lib\UriHelper;
use Core\Validator\Validator;
use DI\Container;
use Exception;

use Slim\Http\ServerRequest as Request;
use Slim\Psr7\UploadedFile;

class ImageService
{
    private FileService $file_service;
    private ImageStrategyInterface $image_manager;

    public function __construct(
        Container $container,
        FileService $file_service
    ) {
        $this->file_service = $file_service;
        $this->image_manager = $container->get(ImageStrategyInterface::class);
    }

    /**
     * 이미지 너비 가져오기
     * @param Request $request
     * @param string|null $path
     * @return int
     */
    public function getImageWidth(Request $request, ?string $path = null): int
    {
        if (empty($path)) {
            return 0;
        }

        try {
            $base_path = UriHelper::getBasePath($request);
            return $this->image_manager->readImage($base_path . $path)->width();
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * 이미지 업로드 처리
     * 
     * @param Request $request 요청 객체
     * @param string $folder 업로드 폴더
     * @param UploadedFile|null $file 업로드 파일
     * @param int $width 이미지 너비
     * @param int $height 이미지 높이
     * @return string 업로드된 파일의 상대 경로
     */
    public function upload(Request $request, string $folder, ?UploadedFile $file, int $width = 0, int $height = 0): string
    {
        if (!Validator::isUploadedFile($file)) {
            return '';
        }

        $is_resize = $width > 0 && $height > 0;
        $directory = $this->file_service->getUploadDir($request, $folder);
        $random_hex = $this->file_service->generateRandomHex();
        $filename = $random_hex . ($is_resize ? "_{$width}x{$height}" : '') . '.' . getExtension($file);
        
        $this->file_service->createDirectory($directory);

        $image = $this->image_manager->readImage($file->getFilePath());
        if ($is_resize) {
            $image->resize($width, $height);
        }
        $image->save($directory . '/' . $filename);

        return $this->file_service->getRelativeFilePath($filename, $folder);
    }
}