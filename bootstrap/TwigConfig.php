<?php

namespace Bootstrap;

use App\Admin\Service\ThemeService;
use App\Config\ConfigService;
use Core\FileService;
use Slim\Views\Twig;
use Twig\Extra\Html\HtmlExtension;
use Twig\Extra\Intl\IntlExtension;
use Core\Extension\CsrfExtension;
use Core\Extension\FlashExtension;
use DI\Container;

class TwigConfig
{
    private const THEME_DIRECTORY = __DIR__ . '/../' . ThemeService::DIRECTORY;
    private const CACHE_DIRECTORY = __DIR__ . '/../data/cache/twig';

    /**
     * Twig 설정을 구성합니다.
     * 
     * @param Container $container
     * @return Twig
     */
    public static function configure(Container $container): Twig
    {
        self::initializeThemeDirectory();

        $config_service = $container->get(ConfigService::class);
        $file_service = $container->get(FileService::class);
        $theme_service = new ThemeService($file_service);
        $theme = $config_service->getTheme();

        if (!$theme_service->existsTheme($theme)) {
            $theme = ThemeService::DEFAULT_THEME;
            $config_service->update(['cf_theme' => $theme]);
        }

        $template_dir = str_replace('\\', '/', self::THEME_DIRECTORY . "/$theme");

        FileService::createDirectory(self::CACHE_DIRECTORY);

        $twig = Twig::create($template_dir, ['cache' => self::CACHE_DIRECTORY, 'auto_reload' => true]);
        self::addExtensions($twig, $container);

        return $twig;
    }

    /**
     * 테마 디렉토리를 초기화합니다.
     * 
     * @return void
     */
    private static function initializeThemeDirectory(): void
    {
        ThemeService::setBaseDir(self::THEME_DIRECTORY);
    }

    /**
     * Twig에 확장 기능을 추가합니다.
     * 
     * @param Twig $twig
     * @param Container $container
     * @return void
     */
    private static function addExtensions(Twig $twig, Container $container): void
    {
        $twig->addExtension(new CsrfExtension($container->get('csrf')));
        $twig->addExtension(new FlashExtension($container->get('flash')));
        $twig->addExtension(new HtmlExtension());
        $twig->addExtension(new IntlExtension());
    }
}
