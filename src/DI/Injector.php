<?php

namespace Phpidy\DI;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Injector
{
    private static $container;

    public static function initialize()
    {
        self::$container = new ContainerBuilder();
        $loader = new YamlFileLoader(self::$container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yml');

        self::$container->compile(true);
    }

    public static function getContainer(): ContainerInterface
    {
        return self::$container;
    }
}