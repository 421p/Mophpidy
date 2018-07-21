<?php

namespace Mophpidy\Command;

use Mophpidy\Logging\Log;
use Symfony\Component\Finder\Finder;

class CommandHolder implements \IteratorAggregate
{
    private $cache = [];

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

        $finder->files()->in(__DIR__.'/../commands');

        $commands = [];

        foreach ($finder as $file) {
            $commands[] = require $file->getRealPath();
        }

        Log::info('{amount} commands loaded.', ['amount' => count($commands)]);

        return $commands;
    }

    /**
     * @return \Iterator|Command[]
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->getCommands());
    }
}
