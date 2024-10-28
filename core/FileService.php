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
     * 파일 디렉토리 생성
     * @param string $directory
     * @return void
     */
    public static function createDirectory(string $directory, $permission = self::PERMISSION): void
    {
        if (!is_dir($directory)) {
            if (!mkdir($directory, $permission, true) && !is_dir($directory)) {
                throw new \RuntimeException(sprintf('디렉토리를 생성할 수 없습니다: %s', $directory));
            }
        }
    }

    /**
     * 주어진 바이트 길이의 랜덤한 16진수 문자열을 생성
     * @param int $length 바이트 길이
     * @return string 랜덤한 16진수 문자열
     * @throws \Exception
     */
    public static function generateRandomHex(int $length = 16): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * 업로드 기본 경로 가져오기
     * @param Request $request
     * @param string|null $add_dir 추가 경로 (옵션)
     * @return string
     */
    public function getUploadDir(Request $request, ?string $add_dir = null): string
    {
        $array_path = [UriHelper::getBasePath($request), $this::UPLOAD_DIRECTORY];
        if (!empty($add_dir)) {
            $array_path[] = trim($add_dir, '/');
        }

        return rtrim(implode('/', $array_path), '/');
    }

    /**
     * 업로드 상대 경로 가져오기
     * @param string $filename 파일명
     * @param string|null $add_dir 추가 경로 (옵션)
     * @return string
     */
    public function getRelativeFilePath(string $filename, ?string $add_dir = null): string
    {
        if (empty($filename)) {
            return '';
        }

        $array_path = [$this::UPLOAD_DIRECTORY];
        if (!empty($add_dir)) {
            $array_path[] = trim($add_dir, '/');
        }
        $array_path[] = $filename;

        return '/' . implode('/', $array_path);
    }

    /**
     * 파일 업로드
     * 
     * @param Request $request 요청 객체
     * @param string $folder 업로드 폴더
     * @param UploadedFileInterface|null $file 업로드 파일
     * @return string 업로드된 파일의 상대 경로
     */
    public function upload(Request $request, string $folder, ?UploadedFileInterface $file, ?string $basename = null): string
    {
        if (!Validator::isUploadedFile($file)) {
            return '';
        }

        $directory = $this->getUploadDir($request, $folder);

        $this->createDirectory($directory);

        $filename = $this->moveUploadedFile($directory, $file, $basename);
        if (empty($filename)) {
            return '';
        }

        return $this->getRelativeFilePath($filename, $folder);
    }

    /**
     * 파일 삭제
     * @param string $path 파일 경로
     * @return void
     */
    public function delete(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * 파일 삭제 (DB 경로)
     * @param Request $request
     * @param string $path 파일 경로
     * @return void
     */
    public function deleteByDb(Request $request, ?string $path = null): void
    {
        if (!empty($path)) {
            $this->delete(UriHelper::getBasePath($request) . $path);
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
    private function moveUploadedFile(string $directory, UploadedFileInterface $uploadedFile, ?string $basename = null): string
    {
        $extension = getExtension($uploadedFile);
        $basename = $basename ?: $this->generateRandomHex() . time();
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $uploadedFile->moveTo($directory . '/' . $filename);

        return $filename;
    }

    /**
     * ZIP 파일 압축 해제
     * @param string $file_path ZIP 파일 경로
     * @param string $extract_path 압축 해제 경로
     * @return bool
     * @throws \Exception
     */
    public function extractZip($file_path, $extract_path): bool
    {
        $zip = new \ZipArchive;
        if ($zip->open($file_path) === TRUE) {
            $zip->extractTo($extract_path);
            $zip->close();
            return true;
        }
        throw new \Exception('ZIP 파일을 해제할 수 없습니다.');
    }

    /**
     * TAR 파일 압축 해제
     * @param string $zip_file_path TAR 파일 경로\
     * @param string $extract_path 압축 해제 경로
     * @return bool
     * @throws \Exception
     */
    public function extractTar($file_path, $extract_path): bool
    {
        try {
            $phar = new \PharData($file_path);
            $phar->extractTo($extract_path, null, true);
            return true;
        } catch (\Exception $e) {
            throw new \Exception('압축 파일을 해제할 수 없습니다: ' . $e->getMessage());
        }
    }

    /**
     * 폴더 내 모든 파일 및 폴더 삭제
     * @param string $dir
     * @return void
     */
    public function clearDirectory($dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object === '.' || $object === '..') {
                continue;
            }

            $object_path = $dir . DIRECTORY_SEPARATOR . $object;
            if (is_dir($object_path)) {
                $this->clearDirectory($object_path);
            } else {
                unlink($object_path);
            }
        }

        rmdir($dir);
    }
}
