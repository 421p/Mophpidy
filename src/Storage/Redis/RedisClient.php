<?php


namespace Mophpidy\Storage\Redis;


use Clue\React\Redis\Client;
use React\Promise\PromiseInterface;

/**
 * Class RedisClient
 *
 * @method PromiseInterface select(string $key)
 * @method PromiseInterface del(string $key)
 * @method PromiseInterface exists(string $key)
 * @method PromiseInterface scan(string $key)
 * @method PromiseInterface get(string $key)
 * @method PromiseInterface set(string $key, string $value)
 */
class RedisClient implements Client
{
    /**
     * @var Client
     */
    private $redis;

    public function __construct(Client $redis)
    {
        return $this->redis = $redis;
    }

    public function on($event, callable $listener)
    {
        return $this->redis->on($event, $listener);
    }

    public function once($event, callable $listener)
    {
        return $this->redis->once($event, $listener);
    }

    public function removeListener($event, callable $listener)
    {
        return $this->redis->removeListener($event, $listener);
    }

    public function removeAllListeners($event = null)
    {
        $this->redis->removeAllListeners($event);
    }

    public function listeners($event = null)
    {
        return $this->redis->listeners($event);
    }

    public function emit($event, array $arguments = [])
    {
        return $this->redis->emit($event, $arguments);
    }

    /**
     * Invoke the given command and return a Promise that will be resolved when the request has been replied to
     *
     * This is a magic method that will be invoked when calling any redis
     * command on this instance.
     *
     * @param string   $name
     * @param string[] $args
     *
     * @return PromiseInterface Promise<mixed, Exception>
     */
    public function __call($name, $args)
    {
        return $this->redis->__call($name, $args);
    }

    /**
     * end connection once all pending requests have been replied to
     *
     * @uses self::close() once all replies have been received
     * @see  self::close() for closing the connection immediately
     */
    public function end()
    {
        return $this->redis->end();
    }

    /**
     * close connection immediately
     *
     * This will emit the "close" event.
     *
     * @see self::end() for closing the connection once the client is idle
     */
    public function close()
    {
        return $this->redis->close();
    }
}