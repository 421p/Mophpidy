<?php

namespace Phpidy\Behaviour;

use Phpidy\DI\Injector;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait ContainerAccess
{
    protected function getContainer(): ContainerInterface
    {
        return Injector::getContainer();
    }

    protected function getParameter(string $name)
    {
        return Injector::getContainer()->getParameter($name);
    }
}