<?php


namespace Mophpidy\Storage\Redis;

use Clue\React\Redis\Factory;
use React\EventLoop\LoopInterface;

class RedisFactory
{
    private $factory;
    
    public function __construct(LoopInterface $loop)
    {
        $this->factory = new Factory($loop);
    }

    public function create(string $url): RedisPromise
    {
        $promise = $this->factory->createClient($url);

        return new RedisPromise($promise);
    }
}