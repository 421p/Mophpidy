<?php

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Api\Player;
use Mophpidy\Command\Command;
use Mophpidy\Telegram\Callback\CallbackContainer;
use Mophpidy\Telegram\Callback\CallbackStorage;

return new class('/favou?rites/i') extends Command
{
    function execute(Update $update, array $matches)
    {
        $storage = $this->getContainer()->get(CallbackStorage::class);

        /** @var Player $player */
        $player = $this->getContainer()->get(Player::class);

        $player->getLibrary()->getFavorites()->then(
            function (array $data) use ($storage, $update) {

                $callback = CallbackContainer::packTracks($data);
                $storage->push($callback);

                $this->sender->sendMessage(
                    [
                        'reply_markup' => [
                            'inline_keyboard' => $callback->mapInlineKeyboard(),
                        ],
                        'chat_id' => $update->getMessage()->getChat()->getId(),
                        'text' => 'List of favorite background songs:',
                    ]
                );
            },
            'dump'
        );
    }
};