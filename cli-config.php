<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Mophpidy\DI\Injector;
use Symfony\Component\Console\Application;

require_once 'autoload.php';

$entityManager = Injector::getContainer()->get(EntityManager::class);

$cli = new Application('Doctrine Command Line Interface', \Doctrine\ORM\Version::VERSION);
$cli->setCatchExceptions(true);

$helperSet = ConsoleRunner::createHelperSet($entityManager);
$helperSet->set(new \Symfony\Component\Console\Helper\QuestionHelper());

$cli->setHelperSet($helperSet);

$cli->addCommands(
    [
        new \Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand(),
        new \Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand(),
        new \Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand(),
        new \Doctrine\DBAL\Migrations\Tools\Console\Command\LatestCommand(),
        new \Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand(),
        new \Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand(),
        new \Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand(),
    ]
);

ConsoleRunner::addCommands($cli);

// Runs console application
$cli->run();
