<?php

namespace Phpidy\Api;

use React\Promise\PromiseInterface;

class Mixer
{
    private $endpoint;

    public function __construct(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    public function setVolume(int $val): PromiseInterface
    {
        return $this->endpoint->ask(
            'core.mixer.set_volume',
            [
                'volume' => $val,
            ]
        );
    }
}