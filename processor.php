#!/usr/bin/env php
<?php

use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Telegram;
use Mophpidy\DI\Injector;
use Mophpidy\Logging\Log;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\LoopInterface;
use React\Http\Response;
use React\Http\Server;

require_once __DIR__.'/autoload.php';

try {
    $container = Injector::getContainer();

    $loop = $container->get(LoopInterface::class);
    $telegram = $container->get(Telegram::class);

    $server = new Server(
        function (ServerRequestInterface $request) use ($telegram) {
            try {
                $data = json_decode($request->getBody()->getContents(), true);

                $update = new Update($data, $telegram->getBotUsername());

                $telegram->processUpdate($update);

                return new Response(204);
            } catch (\Throwable $e) {
                Log::error($e);

                return new Response(500);
            }
        }
    );

    $server->on(
        'error',
        function (Exception $e) {
            Log::error($e);
            if (null !== $e->getPrevious()) {
                $previousException = $e->getPrevious();
                Log::error($previousException);
            }
        }
    );

    $socket = new React\Socket\Server('0.0.0.0:80', $loop);
    $server->listen($socket);

    Log::info('Processing server launched.');

    $loop->run();
} catch (Exception $e) {
    Log::error($e->getMessage());
}
