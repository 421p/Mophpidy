<?php

namespace Mophpidy\Api;

use Evenement\EventEmitterTrait;
use Mophpidy\Logging\Log;
use Ramsey\Uuid\Uuid;
use Ratchet\Client\Connector;
use Ratchet\Client\WebSocket;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

class Endpoint
{
    use EventEmitterTrait;

    const CONNECTED = 'connected';
    const MESSAGE = 'message';

    private $loop;
    private $connector;
    private $uri;

    /** @var WebSocket */
    private $conn;

    public function __construct(string $uri, LoopInterface $loop)
    {
        $this->uri = $uri;
        $this->loop = $loop;
        $this->connector = new Connector($loop);
    }

    public function getUriSchemes(): PromiseInterface
    {
        return $this->ask('core.get_uri_schemes');
    }

    public function connect()
    {
        $this->connectToServer();
    }

    private function connectToServer()
    {
        Log::info('Trying to connect...');

        ($this->connector)($this->uri, [], [])
            ->then(
                \Closure::fromCallable([$this, 'onConnect']),
                function (\Exception $e) {
                    Log::info("Could not connect: {$e->getMessage()}\n");
                    $this->loop->addTimer(3, \Closure::fromCallable([$this, 'connectToServer']));
                }
            );
    }

    public function ask(string $procedure, array $payload = []): PromiseInterface
    {
        $defer = new Deferred();

        $uuid = Uuid::uuid4();

        $listener = function (array $data) use (&$listener, $uuid, $defer) {
            if (isset($data['id']) && $data['id'] === $uuid->toString()) {

                if (array_key_exists('result', $data)) {
                    $defer->resolve($data['result']);
                } else {
                    $defer->reject(new \Exception($data['error']['data']['message']));
                }

                $this->removeListener(self::MESSAGE, $listener);
            }
        };

        $this->on(self::MESSAGE, $listener);

        $this->tell($procedure, $payload, $uuid->toString());

        return $defer->promise();
    }

    public function tell(string $procedure, array $payload = [], $id = null): void
    {
        $sender = function () use ($procedure, $payload, &$sender, $id) {
            if ($this->conn === null) {
                $this->loop->addTimer(1, $sender);
            } else {
                $this->conn->send(
                    json_encode(
                        [
                            'method' => $procedure,
                            'jsonrpc' => '2.0',
                            'params' => $payload,
                            'id' => $id ?? rand(0, 10000),
                        ]
                    )
                );
            }
        };

        $sender();
    }

    private function onConnect(WebSocket $conn)
    {
        Log::info('Successfully connected to the mopidy server.');
        $this->conn = $conn;

        $this->conn->on(
            'message',
            function (\Ratchet\RFC6455\Messaging\MessageInterface $msg) {
                $this->emit(self::MESSAGE, [json_decode($msg->getPayload(), true)]);
            }
        );

        $this->conn->on(
            'close',
            function ($code = null, $reason = null) {
                Log::info("Connection closed ({$code} - {$reason})");
                $this->conn = null;

                //in 3 seconds the app will reconnect
                $this->loop->addTimer(3, \Closure::fromCallable([$this, 'connectToServer']));
            }
        );

        $this->emit(self::CONNECTED);
    }

}