<?php


namespace Mophpidy\Storage\Redis;


use Clue\React\Redis\Client;
use React\Promise\PromiseInterface;

class RedisPromise implements PromiseInterface
{
    private $promise;

    public function __construct(PromiseInterface $promise)
    {
        $this->promise = $promise;
    }

    /**
     *
     * The `$onProgress` argument is deprecated and should not be used anymore.
     *
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onProgress
     *
     * @return void
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        $this->promise->then(function (Client $redis) use ($onFulfilled) {
            return $onFulfilled(new RedisClient($redis));
        }, $onRejected, $onProgress);
    }
}