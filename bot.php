#!/usr/bin/env php
<?php

use Longman\TelegramBot\Telegram;
use Mophpidy\DI\Injector;
use Mophpidy\Logging\Log;
use React\EventLoop\LoopInterface;

require_once __DIR__.'/autoload.php';

try {
    $container = Injector::getContainer();

    $telegram = $container->get(Telegram::class);

    $telegram->handle();

    $loop = $container->get(LoopInterface::class);

    $loop->run();
} catch (Exception $e) {
    Log::error($e->getMessage());
}
