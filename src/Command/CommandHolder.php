<?php

namespace Phpidy\Command;

use Phpidy\Logging\Log;
use Symfony\Component\Finder\Finder;

class CommandHolder implements \IteratorAggregate
{
    public function getCommands(): array
    {
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