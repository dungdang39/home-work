<?php

namespace App\Base\Service;

use Core\FileService;
use DI\Container;
use Slim\Http\ServerRequest as Request;
use Slim\Psr7\UploadedFile;

class ThemeService
{
    public const DEFAULT_THEME = 'basic';
    public const ADMIN_DIRECTORY = 'template/admin';
    public const DIRECTORY = 'template/theme';
    private const SCREENSHOT = 'screenshot.png';
    private const DEFAULT_SCREENSHOT_PATH = '/img/theme_no_screenshot.jpg';
    private const INDEX = 'index.html';
    private const README = 'readme.txt';
    private const THEME_INFO_PATTERNS = [
        'name' => '#^Theme Name:(.+)$#i',
        'uri' => '#^Theme URI:(.+)$#i',
        'maker' => '#^Maker:(.+)$#i',
        'maker_uri' => '#^Maker URI:(.+)$#i',
        'version' => '#^Version:(.+)$#i',
        'detail' => '#^Detail:(.+)$#i',
        'license' => '#^License:(.+)$#i',
        'license_uri' => '#^License URI:(.+)$#i',
    ];

    /**
     * 테마 기본 디렉토리
     */
    private static string $base_dir;

    private Container $container;
    private FileService $file_service;

    public function __construct(
        Container $container,
        FileService $file_service
    ) {
        $this->container = $container;
        $this->file_service = $file_service;
    }

    /**
     * 설치된 테마 목록을 반환합니다.
     * 
     * @return array 테마 목록
     */
    public function getInstalledThemes(): array
    {
        $themes = [];
        $themes_dirs = $this->getThemeDirectories();

        // 테마 정보 설정
        foreach ($themes_dirs as $theme) {
            $themes[] = $this->getThemeInfomation($theme);
        }

        return $themes;
    }

    /**
     * 테마 목록을 파일로 필터링합니다.
     * - index.php와 readme.txt 파일이 있는 디렉토리만 테마로 인식합니다.
     * 
     * @return array 필터링된 테마 목록
     */
    public function getThemeDirectories(): array
    {
        $base_dir = $this->getBaseDir();
        $filterd_themes = array_filter(
            scandir($base_dir),
            function ($theme) use ($base_dir) {
                $theme_dir = $base_dir . "/" . $theme;
                if (
                    in_array($theme, ['.', '..'])
                    || !is_dir($theme_dir)
                ) {
                    return false;
                }

                return (
                    is_file($theme_dir . '/' . self::INDEX)
                    && is_file($theme_dir . '/' . self::README)
                );
            }
        );
        natsort($filterd_themes);

        return array_values($filterd_themes);
    }

    /**
     * 테마 정보를 설정합니다.
     * 
     * @param string $theme 테마 이름
     * @return array 테마 정보
     */
    public function getThemeInfomation(string $theme): array
    {
        $theme_info = [
            'theme' => $theme,
            'screenshot' => '',
            'theme_name' => $theme,
            'theme_uri' => '',
            'maker' => '',
            'maker_uri' => '',
            'version' => '',
            'detail' => '',
            'license' => '',
            'license_uri' => '',
            'is_default' => $theme === self::DEFAULT_THEME,
        ];

        $theme_dir = $this->getBaseDir() . '/' . $theme;

        if (!is_dir($theme_dir)) {
            return $theme_info;
        }

        $theme_info['screenshot'] = $this->getScreenshotUrl($theme_dir, $theme);
        $theme_info = array_merge($theme_info, $this->parseReadmeFile($theme_dir));

        return $theme_info;
    }

    /**
     * 테마가 존재하는지 확인
     * @param string $theme 테마 이름
     * @return bool
     */
    public function existsTheme(string $theme): bool
    {
        return in_array($theme, $this->getThemeDirectories());
    }

    /**
     * 테마 파일 압축 해제
     * @param UploadedFile $uploaded_file 업로드 파일
     * @return bool
     */
    public function extractTheme(UploadedFile $uploaded_file): bool
    {
        $base_path = $this->container->get(Request::class)->getAttribute('base_path');
        $theme_folder_name = pathinfo($uploaded_file->getClientFilename(), PATHINFO_FILENAME);
        $theme_folder_path = join(DIRECTORY_SEPARATOR, [$base_path, self::DIRECTORY, $theme_folder_name]);
        $zip_file_path = $uploaded_file->getFilePath();
        $file_extension = getExtension($uploaded_file);

        if (is_dir($theme_folder_path)) {
            throw new \Exception('동일한 이름의 테마가 이미 설치되어 있습니다.');
        }

        switch ($file_extension) {
            case 'zip':
                return $this->file_service->extractZip($zip_file_path, $theme_folder_path);

            case 'tar':
            case 'gz':
            case 'tgz':
                return $this->file_service->extractTar($zip_file_path, $theme_folder_path);

            default:
                throw new \Exception('지원되지 않는 압축 파일 형식입니다.');
        }
    }

    /**
     * 필수 파일 확인
     * @param string $theme_folder 테마 폴더 경로
     * @return bool
     * @throws \Exception
     */
    public function checkRequiredFiles(string $theme_folder): bool
    {
        $required = [self::INDEX, self::README, self::SCREENSHOT];
        $base_path = $this->container->get(Request::class)->getAttribute('base_path');
        $theme_folder_path = join(DIRECTORY_SEPARATOR, [$base_path, self::DIRECTORY, $theme_folder]);

        foreach ($required as $file) {
            if (!file_exists($theme_folder_path . DIRECTORY_SEPARATOR . $file)) {
                $this->file_service->clearDirectory($theme_folder_path);
                throw new \Exception($file . ' 파일이 누락되었습니다.');
            }
        }
        return true;
    }

    /**
     * 스크린샷 URL을 반환합니다.
     * 
     * @param string $theme 테마 이름
     * @return string 스크린샷 URL
     */
    private function getScreenshotUrl(string $theme_dir, string $theme): string
    {
        $screenshot_path = $theme_dir . '/' . self::SCREENSHOT;
        if (is_file($screenshot_path)) {
            $size = @getimagesize($screenshot_path);
            if ($size && $size[2] == IMAGETYPE_PNG) {
                return '/' . self::DIRECTORY . '/' . $theme . '/' . self::SCREENSHOT;
            }
        }
        return self::DEFAULT_SCREENSHOT_PATH;
    }

    /**
     * README 파일을 파싱하여 테마 정보를 설정합니다.
     * 
     * @param string $theme_dir README 파일 경로
     * @return array
     */
    private function parseReadmeFile(string $theme_dir): array
    {
        $readme_path = $theme_dir . '/' . self::README;
        if (!is_file($readme_path)) {
            return [];
        }

        $content = file($readme_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $theme_info = [];

        foreach (self::THEME_INFO_PATTERNS as $key => $pattern) {
            $line = array_shift($content);
            if ($line === null) {
                break;
            }
            if (preg_match($pattern, $line, $matches)) {
                $theme_info[$key] = trim($matches[1]);
            }
        }

        return $theme_info;
    }

    // ========================================
    // Database Queries
    // ========================================

    // ========================================
    // Getters and Setters
    // ========================================

    public static function getBaseDir(): string
    {
        return self::$base_dir;
    }

    public static function setBaseDir(string $dir): void
    {
        self::$base_dir = $dir;
    }
}
