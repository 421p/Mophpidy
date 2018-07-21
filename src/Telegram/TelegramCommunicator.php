<?php

namespace Mophpidy\Telegram;

use Mophpidy\Api\Playback;
use Mophpidy\Api\TrackList;
use React\EventLoop\LoopInterface;
use React\HttpClient\Client;
use React\HttpClient\Response;
use React\Promise as When;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

class TelegramCommunicator
{
    private $apiKey;
    private $http;
    private $keyboard;
    private $trackList;
    private $playback;

    public function __construct(
        string $apiKey,
        LoopInterface $loop,
        array $defaultKeyboard,
        TrackList $trackList,
        Playback $playback
    ) {
        $this->playback = $playback;
        $this->trackList = $trackList;
        $this->apiKey = $apiKey;
        $this->http = new Client($loop);
        $this->keyboard = $defaultKeyboard;
    }

    public function getKeyboard(): PromiseInterface
    {
        $defer = new Deferred();

        When\all(
            [
                $this->trackList->getSingle(),
                $this->trackList->getRepeat(),
                $this->playback->getState(),
            ]
        )->then(
            function (array $values) use ($defer) {
                $single = true === array_shift($values) ? '✅ Single' : '❌ Single';
                $repeat = true === array_shift($values) ? '✅ Repeat' : '❌ Repeat';

                $state = array_shift($values);

                switch ($state) {
                    case Playback::STATE_PAUSED:
                        $pauseButton = '▶️Resume';
                        $playButton = '⏹ Stop';
                        break;
                    case  Playback::STATE_PLAYING:
                        $pauseButton = '⏸️Pause';
                        $playButton = '⏹ Stop';
                        break;
                    case Playback::STATE_STOPPED:
                        $pauseButton = '⏸️Pause';
                        $playButton = '▶️Play';
                        break;
                    default:
                        throw new \Exception('Unknown state: '.$state);
                }

                $keyboard = $this->keyboard;

                $keyboard[1][1] = $playButton;
                $keyboard[1][2] = $pauseButton;

                array_push($keyboard[2], $single, $repeat);

                $defer->resolve($keyboard);
            },
            \Closure::fromCallable([$defer, 'reject'])
        );

        return $defer->promise();
    }

    public function sendMessageWithDefaultKeyboard(array $payload): PromiseInterface
    {
        $defer = new Deferred();

        $this->getKeyboard()->then(
            function (array $keyboard) use ($defer, $payload) {
                $payload['reply_markup'] = ['keyboard' => $keyboard];

                $this->sendMessage($payload)->then(
                    \Closure::fromCallable([$defer, 'resolve']),
                    \Closure::fromCallable([$defer, 'reject'])
                );
            },
            \Closure::fromCallable([$defer, 'reject'])
        );

        return $defer->promise();
    }

    public function deleteMessage(int $chatId, int $messageId): PromiseInterface
    {
        return $this->action(
            [
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ],
            'deleteMessage'
        );
    }

    public function editMessageReplyMarkup(int $chatId, int $messageId, array $markup): PromiseInterface
    {
        $payload = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'reply_markup' => $markup,
        ];

        $payload['reply_markup'] = json_encode($payload['reply_markup']);

        return $this->action(
            $payload,
            'editMessageReplyMarkup'
        );
    }

    public function answerCallbackQuery(string $id, array $payload = []): PromiseInterface
    {
        $payload['callback_query_id'] = $id;

        return $this->action($payload, 'answerCallbackQuery');
    }

    public function sendMessage(array $payload): PromiseInterface
    {
        if (isset($payload['reply_markup'])) {
            $payload['reply_markup'] = json_encode($payload['reply_markup']);
        }

        return $this->action($payload, 'sendMessage');
    }

    private function action(array $payload, string $action): PromiseInterface
    {
        $data = json_encode($payload);
        $defer = new Deferred();

        $request = $this->http->request(
            'POST',
            sprintf('https://api.telegram.org/bot%s/%s', $this->apiKey, $action),
            [
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($data),
            ]
        );

        $request->on(
            'response',
            function (Response $response) use ($defer, $data) {
                $stream = fopen('php://memory', 'rw');

                $response->on(
                    'data',
                    function ($chunk) use ($stream) {
                        fwrite($stream, $chunk);
                    }
                );

                $response->on(
                    'end',
                    function () use ($stream, $defer, $data) {
                        rewind($stream);

                        $response = json_decode(stream_get_contents($stream), true);

                        if (true === $response['ok']) {
                            $defer->resolve($response['result']);
                        } else {
                            $defer->reject(new \Exception($response['description'].' Payload: '.$data));
                        }

                        fclose($stream);
                    }
                );
            }
        );

        $request->end($data);

        return $defer->promise();
    }
}
