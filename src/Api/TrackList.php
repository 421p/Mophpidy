<?php

namespace Mophpidy\Api;

use React\Promise\PromiseInterface;

class TrackList
{
    private $endpoint;

    public function __construct(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    public function getTracks(): PromiseInterface
    {
        return $this->endpoint->ask('core.tracklist.get_tracks');
    }

    public function getSingle(): PromiseInterface
    {
        return $this->endpoint->ask('core.tracklist.get_single');
    }

    public function getRepeat(): PromiseInterface
    {
        return $this->endpoint->ask('core.tracklist.get_repeat');
    }

    public function setSingle(bool $value): PromiseInterface
    {
        return $this->endpoint->ask('core.tracklist.set_single', ['value' => $value]);
    }

    public function setRepeat(bool $value): PromiseInterface
    {
        return $this->endpoint->ask('core.tracklist.set_repeat', ['value' => $value]);
    }

    public function clear(): PromiseInterface
    {
        return $this->endpoint->ask('core.tracklist.clear');
    }

    public function add(...$uris): PromiseInterface
    {
        return $this->endpoint->ask(
            'core.tracklist.add',
            [
                'tracks' => null,
                'at_position' => null,
                'uri' => null,
                'uris' => $uris,
            ]
        );
    }
}