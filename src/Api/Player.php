<?php

namespace Mophpidy\Api;

use Mophpidy\Entity\User;
use Mophpidy\Logging\Log;
use Mophpidy\Storage\Storage;
use Mophpidy\Telegram\TelegramCommunicator;
use React\Promise as When;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

class Player
{
    const TRACK_PLAYBACK_STARTED = 'track_playback_started';
    const PLAYBACK_STATE_CHANGED = 'playback_state_changed';

    private $library;
    private $list;
    private $playback;
    private $mixer;
    private $endpoint;
    private $sender;
    private $storage;

    private $eventsCache = [
        self::TRACK_PLAYBACK_STARTED => null,
        self::PLAYBACK_STATE_CHANGED => null,
    ];

    public function __construct(
        Library $library,
        TrackList $list,
        Playback $playback,
        Mixer $mixer,
        Endpoint $endpoint,
        TelegramCommunicator $sender,
        Storage $storage
    )
    {
        $this->library  = $library;
        $this->list     = $list;
        $this->playback = $playback;
        $this->mixer    = $mixer;
        $this->endpoint = $endpoint;
        $this->sender   = $sender;
        $this->storage  = $storage;
    }

    public function getQueue(): PromiseInterface
    {
        return $this->list->getTracks();
    }

    public function listenGeneralEvents()
    {
        $this->listenMopidyEvent(
            self::TRACK_PLAYBACK_STARTED,
            function (array $data) {
                $track = $data['tl_track']['track'];

                if ($this->eventsCache[self::TRACK_PLAYBACK_STARTED] !== $track) {
                    $this->eventsCache[self::TRACK_PLAYBACK_STARTED] = $track;

                    $text = str_replace(
                        '.mp3',
                        '',
                        array_key_exists('artists', $track) ?
                            sprintf(
                                'Current track: %s - %s',
                                $track['artists'][0]['name'],
                                $track['name']
                            ) : sprintf('Current track: %s', $track['name'])
                    );

                    $this->storage->forEachNotificationSubscriber(
                        function (User $user) use ($text) {
                            $this->sender->sendMessageWithDefaultKeyboard(
                                [
                                    'chat_id' => $user->getId(),
                                    'text'    => $text,
                                ]
                            )->then(
                                null,
                                function (\Throwable $e) {
                                    Log::error($e);
                                }
                            );
                        }
                    );
                }
            }
        );

        $this->listenMopidyEvent(
            self::PLAYBACK_STATE_CHANGED,
            function ($data) {
                $this->storage->forEachNotificationSubscriber(
                    function (User $user) use ($data) {
                        if ($data['old_state'] !== $data['new_state']) {
                            $this->sender->sendMessageWithDefaultKeyboard(
                                [
                                    'chat_id' => $user->getId(),
                                    'text'    => sprintf(
                                        'Stage changed from %s to %s',
                                        $data['old_state'],
                                        $data['new_state']
                                    ),
                                ]
                            )->then(
                                null,
                                function (\Throwable $e) {
                                    Log::error($e);
                                }
                            );
                        }
                    }
                );
            }
        );
    }

    public function listenMopidyEvent(string $event, callable $closure)
    {
        $this->endpoint->on(
            Endpoint::MESSAGE,
            function ($data) use ($event, $closure) {
                if (isset($data['event']) && $data['event'] === $event) {
                    unset($data['event']);

                    $closure($data);
                }
            }
        );
    }

    public function listenMopidyEventOnce(string $event, callable $closure)
    {
        $listener = function ($data) use ($event, $closure, &$listener) {
            if (isset($data['event']) && $data['event'] === $event) {
                unset($data['event']);

                $closure($data);
                $this->endpoint->removeListener(Endpoint::MESSAGE, $listener);
            }
        };

        $this->endpoint->on(Endpoint::MESSAGE, $listener);
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

        When\all(
            [
                $this->list->clear(),
                $this->list->add($uri),
            ]
        )->then(
            function () use ($defer, $uri) {
                Log::info('Added track with uri: ' . $uri . ' to playback.');

                $this->playback->play()->then(\Closure::fromCallable([$defer, 'resolve']));
            }
        );

        return $defer->promise();
    }

    public function playList(array $uris)
    {
        $defer = new Deferred();

        When\all(
            [
                $this->list->clear(),
                $this->list->add(...$uris),
            ]
        )->then(
            function () use ($defer) {
                Log::info('Added tracks to playback.');

                $this->playback->play()->then(
                    \Closure::fromCallable([$defer, 'resolve']),
                    \Closure::fromCallable([$defer, 'reject'])
                );
            },
            \Closure::fromCallable([$defer, 'reject'])
        );

        return $defer->promise();
    }

    public function getState(): PromiseInterface
    {
        return $this->playback->getState();
    }

    public function setVolume(int $val): PromiseInterface
    {
        return $this->mixer->setVolume($val);
    }

    public function getVolume(): PromiseInterface
    {
        return $this->mixer->getVolume();
    }

    public function play(): PromiseInterface
    {
        return $this->playback->play();
    }

    public function stop(): PromiseInterface
    {
        return $this->playback->stop();
    }

    public function resume(): PromiseInterface
    {
        return $this->playback->resume();
    }

    public function next(): PromiseInterface
    {
        return $this->playback->next();
    }

    public function previous(): PromiseInterface
    {
        return $this->playback->previous();
    }

    public function pause(): PromiseInterface
    {
        return $this->playback->pause();
    }

    public function getCurrentTlId(): PromiseInterface
    {
        return $this->playback->getCurrentTlId();
    }

    public function getCurrentTrack(): PromiseInterface
    {
        return $this->playback->getCurrentTrack();
    }

    public function getLibrary(): Library
    {
        return $this->library;
    }

    public function getTrackList(): TrackList
    {
        return $this->list;
    }
}
