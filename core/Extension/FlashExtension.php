<?php

namespace Core\Extension;

use DI\Container;
use Twig\Error\RuntimeError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class FlashExtension extends AbstractExtension
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('old', [$this, 'old']),
        ];
    }

    public function old(string $key = null, string $default = ''): string
    {
        $flashData = $_SESSION['_flash.new'] ?? [];

        if ($key === null) {
            return $flashData;
        }

        return $flashData[$key] ?? $default;

        // throw new RuntimeError(\sprintf('The html_classes function argument %d (key %d) should be a string, got "%s".', $i, $class, \gettype($class)));
    }
}
