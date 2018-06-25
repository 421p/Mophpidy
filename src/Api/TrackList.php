<?php

namespace Phpidy\Api;

use React\Promise\PromiseInterface;

class TrackList
{
    private $endpoint;

    public function __construct(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    public function clear(): PromiseInterface
    {
        return $this->endpoint->ask('core.tracklist.clear');
    }

    public function add(string $uri): PromiseInterface
    {
        return $this->endpoint->ask(
            'core.tracklist.add',
            [
                'tracks' => null,
                'at_position' => null,
                'uri' => null,
                'uris' => [$uri],
            ]
        );
    }
}