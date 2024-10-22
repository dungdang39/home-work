<?php

namespace Bootstrap;

use App\Base\Service\ThemeService;
use App\Base\Service\ConfigService;
use Core\FileService;
use Slim\Views\Twig;
use Twig\Extra\Html\HtmlExtension;
use Twig\Extra\Intl\IntlExtension;
use Core\Extension\CsrfExtension;
use Core\Extension\FlashExtension;
use DI\Container;
use Twig\Loader\FilesystemLoader;

class TwigConfig
{
    private const CACHE_DIRECTORY = 'data/cache/twig';

    /**
     * Twig 설정을 구성합니다.
     * 
     * @param Container $container
     * @return Twig
     */
    public static function configure(Container $container): Twig
    {
        self::initializeThemeDirectory(ThemeService::DIRECTORY);

        $theme = self::getTheme($container);

        FileService::createDirectory(self::CACHE_DIRECTORY);

        $twig = Twig::create(
            [
                'admin' => ThemeService::ADMIN_DIRECTORY,
                FilesystemLoader::MAIN_NAMESPACE => ThemeService::DIRECTORY . '/' . $theme,
            ],
            [
                'cache' => self::CACHE_DIRECTORY,
                'auto_reload' => true
            ]
        );
        self::addExtensions($twig, $container);

        return $twig;
    }

    private static function getTheme(Container $container): string
    {
        $config_service = $container->get(ConfigService::class);
        $theme_service = $container->get(ThemeService::class);
        $theme = $config_service->getTheme();

        if ($theme_service->existsTheme($theme)) {
            return $theme;
        }

        $config_service->update('design', 'theme', $theme);

        return ThemeService::DEFAULT_THEME;

    }

    /**
     * 테마 디렉토리를 초기화합니다.
     * 
     * @return void
     */
    private static function initializeThemeDirectory(string $theme_directory): void
    {
        ThemeService::setBaseDir($theme_directory);
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
