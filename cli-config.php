<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Mophpidy\DI\Injector;

require_once 'autoload.php';

$entityManager = Injector::getContainer()->get(EntityManager::class);

return ConsoleRunner::createHelperSet($entityManager);