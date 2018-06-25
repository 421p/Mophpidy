<?php

namespace Phpidy\Api;

use Phpidy\Logging\Log;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use function React\Promise\all;

class Player
{
    private $library;
    private $list;
    private $playback;
    private $mixer;

    public function __construct(Library $library, TrackList $list, Playback $playback, Mixer $mixer)
    {
        $this->library = $library;
        $this->list = $list;
        $this->playback = $playback;
        $this->mixer = $mixer;
    }

    public function playSingleTrack(string $album, string $name): PromiseInterface
    {
        $defer = new Deferred();

        $this->library->findTrack($album, $name)->then(
            function (array $data) use ($defer) {
                $this->playSingleUri($data['uri'])->then(\Closure::fromCallable([$defer, 'resolve']));
            }
        );

        return $defer->promise();
    }

    public function playSingleUri(string $uri): PromiseInterface
    {
        $defer = new Deferred();

        all(
            [
                $this->list->clear(),
                $this->list->add($uri),

            ]
        )->then(
            function () use ($defer, $uri) {
                Log::info('Added track with uri: '.$uri.' to playback.');

                $this->playback->play()->then(\Closure::fromCallable([$defer, 'resolve']));
            }
        );

        return $defer->promise();
    }

    public function setVolume(int $val): PromiseInterface
    {
        return $this->mixer->setVolume($val);
    }

    public function play(): PromiseInterface
    {
        return $this->playback->play();
    }

    public function pause(): PromiseInterface
    {
        return $this->playback->pause();
    }

    public function getLibrary(): Library
    {
        return $this->library;
    }
}