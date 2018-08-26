<?php

namespace Mophpidy\Telegram;

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Logging\Log;
use Mophpidy\Telegram\Command\CallbackqueryCommand;
use Mophpidy\Telegram\Command\GenericCommand;
use Mophpidy\Telegram\Command\GenericmessageCommand;
use Mophpidy\Telegram\Command\RequestAccessCommand;
use Mophpidy\Telegram\Command\StartCommand;
use Psr\Container\ContainerInterface;
use React\EventLoop\LoopInterface;
use React\HttpClient\Client;
use React\HttpClient\Response;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

class Telegram extends \Longman\TelegramBot\Telegram
{
    private $loop;
    private $http;

    private $offset  = 0;
    private $limit   = 100;
    private $timeout = 120;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(
        string $api_key,
        string $bot_username,
        LoopInterface $loop,
        ContainerInterface $container
    )
    {
        $this->loop = $loop;
        parent::__construct($api_key, $bot_username);

        $this->http = new Client($loop);
        $this->container = $container;
    }

    public function handle()
    {
        $this->getUpdates(
            [
                'offset'  => $this->offset,
                'limit'   => $this->limit,
                'timeout' => $this->timeout,
            ]
        )->then(
            function (array $data) {
                foreach ($data['result'] as $raw) {
                    try {
                        $update = new Update($raw, $this->getBotUsername());

                        $this->processUpdate($update);

                        $this->offset = $update->getUpdateId() + 1;
                    } catch (\Throwable $e) {
                        Log::error($e);
                    }
                }

                $this->handle();
            },
            function (\Exception $e) {
                Log::error($e);
            }
        );
    }

    public function getCommandObject($command) {
        $command = $this->createCommand($command);

        if ($command !== null) {
            $command->setContainer($this->container);
        }

        return $command;
    }

    protected function createCommand($command)
    {
        switch ($command) {
            case 'generic':
                return new GenericCommand($this, $this->update);
            case 'genericmessage':
                return new GenericmessageCommand($this, $this->update);
            case 'start':
                return new StartCommand($this, $this->update);
            case 'request_access':
                return new RequestAccessCommand($this, $this->update);
            case 'callbackquery':
                return new CallbackqueryCommand($this, $this->update);
            default:
                return null;
        }
    }

    private function getUpdates(array $payload = []): PromiseInterface
    {
        $data  = json_encode($payload);
        $defer = new Deferred();

        $request = $this->http->request(
            'POST',
            sprintf('https://api.telegram.org/bot%s/getUpdates', $this->getApiKey()),
            [
                'Content-Type'   => 'application/json',
                'Content-Length' => strlen($data),
            ]
        );

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

                        $defer->resolve(json_decode(stream_get_contents($stream), true));

                        fclose($stream);
                    }
                );
            }
        );

        $request->end($data);

        return $defer->promise();
    }
}
