<?php

namespace Mophpidy\Api;

use React\Promise\PromiseInterface;

class Playback
{
    const STATE_PLAYING = 'playing';
    const STATE_PAUSED = 'paused';
    const STATE_STOPPED = 'stopped';

    private $endpoint;

    public function __construct(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    public function play($tlid = null, $tltrack = null): PromiseInterface
    {
        return $this->endpoint->ask(
            'core.playback.play',
            [
                'tlid' => $tlid,
                'tl_track' => $tltrack,
            ]
        );
    }

    public function getState(): PromiseInterface
    {
        return $this->endpoint->ask('core.playback.get_state');
    }

    public function next(): PromiseInterface
    {
        return $this->endpoint->ask('core.playback.next');
    }

    public function previous(): PromiseInterface
    {
        return $this->endpoint->ask('core.playback.previous');
    }

    public function pause(): PromiseInterface
    {
        return $this->endpoint->ask('core.playback.pause');
    }

    public function stop(): PromiseInterface
    {
        return $this->endpoint->ask('core.playback.stop');
    }

    public function resume(): PromiseInterface
    {
        return $this->endpoint->ask('core.playback.resume');
    }

    public function getCurrentTrack(): PromiseInterface
    {
        return $this->endpoint->ask('core.playback.get_current_track');
    }

    public function getCurrentTlId(): PromiseInterface
    {
        return $this->endpoint->ask('core.playback.get_current_tlid');
    }
}