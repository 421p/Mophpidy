<?php

namespace Mophpidy\Command;

use Mophpidy\Logging\Log;
use Mophpidy\Telegram\TelegramCommunicator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;

class CommandHolder implements \IteratorAggregate
{
    private $cache = [];
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var TelegramCommunicator
     */
    private $sender;

    public function __construct(ContainerInterface $container, TelegramCommunicator $sender)
    {
        $this->container = $container;
        $this->sender = $sender;
    }

    public function cacheCommands()
    {
        $this->cache = $this->getCommands();
    }

    public function getCommands(): array
    {
        if (0 !== count($this->cache)) {
            return $this->cache;
        }

        $finder = new Finder();

        $finder->files()->in(__DIR__.'/templates');

        $commands = [];

        foreach ($finder as $file) {
            /** @var Command $command */
            $command = require $file->getRealPath();

            $command->setContainer($this->container);
            $command->setSender($this->sender);

            $commands[] = $command;
        }

        Log::info('{amount} commands loaded.', ['amount' => count($commands)]);

        return $commands;
    }

    /**
     * @return \Iterator|Command[]
     */
    public function getIterator()
    {
        foreach ($this->getCommands() as $command) {
            yield $command;
        }
    }
}
