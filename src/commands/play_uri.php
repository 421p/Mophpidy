<?php

use Longman\TelegramBot\Entities\Update;
use Phpidy\Api\Player;
use Phpidy\Command\Command;
use Phpidy\Telegram\Callback\CallbackStorage;
use Phpidy\Telegram\Callback\StoredCallback;

return new class('/\/playuri (?<uri>.+)/i') extends Command
{
    function execute(Update $update, array $matches)
    {
        /** @var Player $player */
        $player = $this->getContainer()->get(Player::class);
        $storage = $this->getContainer()->get(CallbackStorage::class);

        $id = $update->getCallbackQuery()->getData();

        /** @var StoredCallback $callback */
        $callback = $storage->get($id);

        $uri = $matches['uri'];

        $player->playSingleUri($uri)->then(
            function () use ($update, $callback) {
                $callbackId = $update->getCallbackQuery()->getId();

                $this->sender->answerCallbackQuery(
                    $callbackId,
                    [
                        'text' => 'Playing '.$callback->getPayload()['name'],
                    ]
                );
            }
        );
    }
};