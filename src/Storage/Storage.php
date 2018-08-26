<?php

namespace Mophpidy\Storage;

use Doctrine\Common\Inflector\Inflector;
use Mophpidy\Entity\CallbackContainer;
use Mophpidy\Entity\User;
use Mophpidy\Storage\Redis\RedisClient as Redis;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use function Functional\compose;
use function Functional\const_function;
use function Functional\equal;
use function Functional\match;

/**
 * @method PromiseInterface userStorage(callable $closure)
 * @method PromiseInterface callbackStorage(callable $closure)
 * @method PromiseInterface getUser(int $id)
 * @method PromiseInterface getCallback(string $id)
 * @method PromiseInterface hasUser(int $id)
 * @method PromiseInterface hasCallback(string $id)
 * @method void forEachUser(callable $closure)
 */
class Storage
{
    const DB_CALLBACK = 0;
    const DB_USER     = 1;

    private $connections;
    private $reflector;

    /**
     * Storage constructor.
     *
     * @param PromiseInterface<RedisClient, \Throwable> $userSpaceRedis
     * @param PromiseInterface<RedisClient, \Throwable> $callbackSpaceRedis
     */
    public function __construct(
        PromiseInterface $userSpaceRedis,
        PromiseInterface $callbackSpaceRedis
    ) {

        $this->reflector   = new \ReflectionObject($this);

        $callbackDefer = new Deferred();
        $userDefer     = new Deferred();

        $this->connections = [
            self::DB_CALLBACK => $callbackDefer->promise(),
            self::DB_USER => $userDefer->promise()
        ];

        $userSpaceRedis->then(
            function (Redis $client) use ($userDefer) {
                $client->select(self::DB_USER)->then(
                    function () use ($client, $userDefer) {
                        $userDefer->resolve($client);
                    }
                );
            }
        );

        $callbackSpaceRedis->then(
            function (Redis $client) use ($callbackDefer) {
                $client->select(self::DB_CALLBACK)->then(
                    function () use ($client, $callbackDefer) {
                        $callbackDefer->resolve($client);
                    }
                );
            }
        );
    }

    private function getConstant(string $name)
    {
        return $this->reflector->getConstant(sprintf('DB_%s', strtoupper($name)));
    }

    public function __call($name, $arguments)
    {
        $methods = get_class_methods(self::class);

        if (in_array($name, $methods)) {
            return null;
        }

        $tokens = explode('_', Inflector::tableize($name));

        $method = array_pop($tokens);
        $type   = Inflector::camelize(implode('_', $tokens));

        if (in_array($type, ['get', 'forEach', 'has'])) {
            [$method, $type] = [$type, $method];
        }

        if (in_array($method, get_class_methods(self::class))) {

            $arguments[] = $this->getConstant($type);

            return $this->$method(...$arguments);
        } else {
            throw new \BadMethodCallException('Method ' . $name . ' not found.');
        }
    }

    public function storage(callable $closure, int $type): PromiseInterface
    {
        $defer = new Deferred();

        $this->connections[$type]->then(
            function (Redis $client) use ($closure, $defer, $type) {
                $client->select($type)->then(
                    function () use ($closure, $client, $defer) {
                        $defer->resolve($closure($client));
                    }
                );
            }
        );

        return $defer->promise();
    }

    public function forEach(callable $closure, int $type)
    {
        $this->storage(
            function (Redis $client) use ($closure, $type) {

                $scanner = function (int $index = null) use ($client, &$scanner, $closure, $type) {

                    if ($index === 0) {
                        return;
                    }

                    $client->scan($index ?? 0)->then(
                        function (array $data) use (&$scanner, $closure, $type) {
                            $cursor = $data[0];

                            foreach ($data[1] as $key) {
                                $this->get($key, $type)->then($closure);
                            }

                            $scanner($cursor);
                        }
                    );
                };

                $scanner();
            },
            $type
        );
    }

    public function has($id, int $type): PromiseInterface
    {
        $defer = new Deferred();

        $this->storage(
            function (Redis $redis) use ($id) {
                return $redis->exists($id);
            },
            $type
        )->then(
            function ($data) use ($defer) {
                $defer->resolve($data === 0 ? false : true);
            },
            \Closure::fromCallable([$defer, 'reject'])
        );

        return $defer->promise();
    }

    public function get($id, int $type): PromiseInterface
    {
        $defer = new Deferred();

        $this->storage(
            function (Redis $redis) use ($id) {
                return $redis->get($id);
            },
            $type
        )->then(
            function ($data) use ($defer, $type) {

                if ($data === null) {
                    $defer->resolve(null);

                    return;
                }

                try {
                    $class = match(
                        [
                            [equal(self::DB_CALLBACK), const_function(CallbackContainer::class)],
                            [equal(self::DB_USER), const_function(User::class)],
                        ]
                    )(
                        $type
                    );

                    /** @var \Serializable $item */
                    $item = new $class();

                    $item->unserialize($data);

                    $defer->resolve($item);
                } catch (\Throwable $e) {
                    $defer->reject($e);
                }
            }
        );

        return $defer->promise();
    }

    /**
     * @param int $id
     *
     * @return PromiseInterface
     */
    public function addDefaultUser(int $id): PromiseInterface
    {
        $user = new User();

        $user->setId($id);
        $user->setNotification(false);
        $user->setAdmin(false);

        return $this->addOrUpdateUser($user);
    }

    public function addOrUpdateUser(User $user): PromiseInterface
    {
        return $this->userStorage(
            function (Redis $redis) use ($user) {
                return $redis->set($user->getId(), $user->serialize());
            }
        );
    }

    /**
     * @param CallbackContainer $container
     *
     * @return PromiseInterface
     * @throws \Throwable
     */
    public function addOrUpdateCallback(CallbackContainer $container): PromiseInterface
    {
        return $this->callbackStorage(
            function (Redis $redis) use ($container) {
                return $redis->set($container->getId(), $container->serialize());
            }
        );
    }

    /**
     * @param CallbackContainer $container
     *
     * @return PromiseInterface
     */
    public function removeCallback(CallbackContainer $container): PromiseInterface
    {
        return $this->callbackStorage(
            function (Redis $redis) use ($container) {
                return $redis->del($container->getId());
            }
        );
    }

    public function forEachNotificationSubscriber(callable $closure): void
    {
        $this->forEachUser(
            function (User $user) use ($closure) {
                if ($user->shouldBeNotified()) {
                    $closure($user);
                }
            }
        );
    }

    public function forEachAdmin(callable $closure): void
    {
        $this->forEachUser(
            function (User $user) use ($closure) {
                if ($user->isAdmin()) {
                    $closure($user);
                }
            }
        );
    }

    /**
     * @param int $id
     *
     * @return PromiseInterface
     */
    public function disableNotifications(int $id): PromiseInterface
    {
        $defer = new Deferred();

        $this->getUser($id)->then(
            function (User $user) use ($defer) {
                if (false === $user->shouldBeNotified()) {
                    $defer->resolve(false);
                } else {
                    $user->setNotification(false);

                    $this->addOrUpdateUser($user)->then(
                        function () use ($defer) {
                            $defer->resolve(true);
                        }
                    );
                }
            }
        );

        return $defer->promise();
    }

    /**
     * @param int $id
     *
     * @return PromiseInterface
     */
    public function enableNotifications(int $id): PromiseInterface
    {
        $defer = new Deferred();

        $this->getUser($id)->then(
            function (User $user) use ($defer) {
                if (true === $user->shouldBeNotified()) {
                    $defer->resolve(false);
                } else {
                    $user->setNotification(true);

                    $this->addOrUpdateUser($user)->then(
                        function () use ($defer) {
                            $defer->resolve(true);
                        }
                    );
                }
            }
        );

        return $defer->promise();
    }

    /**
     * @param int $id
     *
     * @return PromiseInterface
     */
    public function isUserAllowed(int $id): PromiseInterface
    {
        return $this->hasUser($id);
    }

    public function updateAdmins(): PromiseInterface
    {
        $defer = new Deferred();

        $users = array_map(
            compose('trim', 'intval'),
            explode(',', getenv('ADMIN'))
        );

        foreach ($users as $id) {

            $this->hasUser($id)->then(
                function (bool $exists) use ($id, $defer) {
                    if (!$exists) {
                        $user = new User();
                        $user->setId($id);
                        $user->setNotification(true);
                        $user->setAdmin(true);

                        $this->addOrUpdateUser($user)->then(
                            'dump',
                            'dump'
                        );
                    }
                }
            );
        }

        return $defer->promise();
    }
}
