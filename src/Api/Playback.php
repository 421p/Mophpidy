<?php

namespace Phpidy\Api;

use React\Promise\PromiseInterface;

class Playback
{
    private $endpoint;

    public function __construct(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    public function play($tlid = null, $tltrack = null): PromiseInterface
    {
        return $this->endpoint->ask('core.playback.play', [
            'tlid' => $tlid,
            'tl_track' => $tltrack,
        ]);
    }

    public function pause(): PromiseInterface
    {
        return $this->endpoint->ask('core.playback.pause');
    }
}