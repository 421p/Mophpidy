<?php

namespace Mophpidy\Telegram\Callback;

use React\EventLoop\LoopInterface;

class CallbackStorage
{
    private $callbacks = [];

    public function __construct(LoopInterface $loop)
    {
        $loop->addPeriodicTimer(60, \Closure::fromCallable([$this, 'cleanup']));
    }

    public function push(CallbackContainer $callback)
    {
        $this->callbacks[$callback->getId()] = $callback;
    }

    public function get(string $id): ?CallbackContainer
    {
        return isset($this->callbacks[$id]) ? $this->callbacks[$id] : null;
    }

    public function remove(string $id): ?CallbackContainer
    {
        $callback = $this->get($id);
        unset($this->callbacks[$id]);

        return $callback;
    }

    private function cleanup()
    {
        if (count($this->callbacks) !== 0) {
            /** @var CallbackContainer $callback */
            foreach ($this->callbacks as $i => $callback) {
                if ($callback->getDate()->diff(new \DateTime())->i > 3) {
                    unset($this->callbacks[$i]);
                }
            }
        }
    }
}