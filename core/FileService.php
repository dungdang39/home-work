<?php

namespace Core;

use Core\Lib\UriHelper;
use Core\Validator\Validator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;

class FileService
{
    public const UPLOAD_DIRECTORY = 'data';
    public const PERMISSION = 0755;

    /**
     * 업로드 기본 경로 가져오기
     * @param Request $request
     * @return string
     */
    public static function getUploadDir(Request $request): string
    {
        return UriHelper::getBasePath($request) . '/' . self::UPLOAD_DIRECTORY;
    }

    /**
     * 업로드 상대 경로 가져오기
     * @return string
     */
    public static function getRelativePath(): string
    {
        return '/' . self::UPLOAD_DIRECTORY;
    }

    /**
     * 파일 디렉토리 생성
     * @param string $directory
     * @return void
     */
    public function createUploadDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            if (!mkdir($directory, self::PERMISSION, true) && !is_dir($directory)) {
                throw new \RuntimeException(sprintf('디렉토리를 생성할 수 없습니다: %s', $directory));
            }
        }
    }

    /**
     * 파일 업로드
     * @param string $directory 업로드 디렉토리
     * @param UploadedFileInterface|null $file 업로드 파일
     * @param string|null $basename 업로드할 파일명
     * @return string 업로드된 파일명
     */
    public function uploadFile(string $directory, ?UploadedFileInterface $file, ?string $basename = null): string
    {
        $filename = "";

        $this->createUploadDirectory($directory);

        if (Validator::isUploadedFile($file)) {
            $filename = $this->moveUploadedFile($directory, $file, $basename);
        }

        return $filename;
    }

    /**
     * 파일 삭제
     * @param string $path 파일 경로
     * @return void
     */
    public function deleteFile(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * 업로드 파일 이름 변경 및 이동
     * @param string $directory 업로드 디렉토리
     * @param UploadedFileInterface $uploadedFile 업로드 파일
     * @param string|null $basename 업로드할 파일명
     * @param int $byte 랜덤 바이트 길이
     * @return string 업로드된 파일명
     * @throws \RuntimeException
     */
    private function moveUploadedFile(string $directory, UploadedFileInterface $uploadedFile, ?string $basename = null, int $byte = 16): string
    {
        $extension = getExtension($uploadedFile);
        $basename = $basename ?: bin2hex(random_bytes($byte) . time());
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $uploadedFile->moveTo($directory . '/' . $filename);

        return $filename;
    }
}