<?php

namespace App\Admin\Service;

class ThemeService
{
    public const DEFAULT_THEME = 'basic_test';
    public const DIRECTORY = 'theme';
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

    /**
     * 설치된 테마 목록을 반환합니다.
     * 
     * @return array 테마 목록
     */
    public function getThemes(): array
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
            'license_uri' => ''
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
