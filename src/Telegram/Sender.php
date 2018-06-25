<?php

namespace Phpidy\Telegram;

use React\EventLoop\LoopInterface;
use React\HttpClient\Client;
use React\HttpClient\Response;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

class Sender
{
    private $apiKey;
    private $http;
    private $keyboard;

    public function __construct(string $apiKey, LoopInterface $loop, array $defaultKeyboard)
    {
        $this->apiKey = $apiKey;
        $this->http = new Client($loop);
        $this->keyboard = $defaultKeyboard;
    }

    public function getKeyboard(): array
    {
        return $this->keyboard;
    }

    public function sendMessageWithDefaultKeyboard(array $payload): PromiseInterface
    {
        $payload['reply_markup'] = ['keyboard' => $this->keyboard];

        return $this->sendMessage($payload);
    }

    public function deleteMessage(int $chatId, int $messageId): PromiseInterface
    {
        return $this->action([
            'chat_id' => $chatId,
            'message_id' => $messageId
        ], 'deleteMessage');
    }

    public function answerCallbackQuery(int $id, array $payload): PromiseInterface
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
        );;

        $request->on(
            'response',
            function (Response $response) use ($defer) {

                $stream = fopen('php://memory', 'rw');

                $response->on(
                    'data',
                    function ($chunk) use ($stream) {
                        fwrite($stream, $chunk);
                    }
                );

                $response->on(
                    'end',
                    function () use ($stream, $defer) {
                        rewind($stream);

                        $data = json_decode(stream_get_contents($stream), true);

                        if ($data['ok'] === true) {
                            $defer->resolve($data['result']);
                        } else {
                            $defer->reject(new \Exception($data['description']));
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