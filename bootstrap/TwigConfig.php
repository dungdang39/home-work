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

class TwigConfig
{
    private const CACHE_DIRECTORY = '/data/cache/twig';

    /**
     * Twig 설정을 구성합니다.
     * 
     * @param Container $container
     * @return Twig
     */
    public static function configure(Container $container): Twig
    {
        $theme_directory = dirname(__DIR__) . '/' . ThemeService::DIRECTORY;
        $cache_directory = dirname(__DIR__) . self::CACHE_DIRECTORY;

        self::initializeThemeDirectory($theme_directory);

        $config_service = $container->get(ConfigService::class);
        $theme_service = $container->get(ThemeService::class);
        $theme = $config_service->getTheme();

        if (!$theme_service->existsTheme($theme)) {
            $theme = ThemeService::DEFAULT_THEME;
            $config_service->update('design', 'theme', $theme);
        }

        $template_dir = str_replace('\\', '/', "{$theme_directory}/{$theme}");

        FileService::createDirectory($cache_directory);

        $twig = Twig::create($template_dir, ['cache' => $cache_directory, 'auto_reload' => true]);
        self::addExtensions($twig, $container);

        return $twig;
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
