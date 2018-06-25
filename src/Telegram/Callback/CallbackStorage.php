<?php

namespace Phpidy\Telegram\Callback;

use Phpidy\Logging\Log;
use React\EventLoop\LoopInterface;

class CallbackStorage
{
    private $callbacks = [];

    public function __construct(LoopInterface $loop)
    {
        $loop->addPeriodicTimer(60, \Closure::fromCallable([$this, 'cleanup']));
    }

    public function push(StoredCallback $callback)
    {
        $this->callbacks[$callback->getId()->toString()] = $callback;
    }

    public function get(string $id): ?StoredCallback
    {
        return @$this->callbacks[$id];
    }

    private function cleanup()
    {
        Log::info('Cleaning up storage, current amount of stored callbacks: '.count($this->callbacks));

        /** @var StoredCallback $callback */
        foreach ($this->callbacks as $i => $callback) {
            if ($callback->getDate()->diff(new \DateTime())->i > 3) {
                unset($this->callbacks[$i]);
            }
        }
    }
}