<?php

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Api\Player;
use Mophpidy\Command\Command;
use Mophpidy\Entity\CallbackContainer;
use Mophpidy\Storage\Storage;

return new class('/favou?rites/i') extends Command {
    public function execute(Update $update, array $matches, CallbackContainer $callback = null)
    {
        $storage = $this->getContainer()->get(Storage::class);

        /** @var Player $player */
        $player = $this->getContainer()->get(Player::class);

        $player->getLibrary()->getFavorites()->then(
            function (array $data) use ($storage, $update) {
                $callback = CallbackContainer::pack(
                    $data,
                    CallbackContainer::TRACKS,
                    $update->getMessage()->getChat()->getId()
                );

                $handler = function (array $data) use ($storage, $callback) {
                    $callback->setMessageId($data['message_id']);
                    $storage->addOrUpdateCallback($callback);
                };

                $this->sender->sendMessage(
                    [
                        'reply_markup' => [
                            'inline_keyboard' => $callback->mapInlineKeyboard(),
                        ],
                        'chat_id' => $update->getMessage()->getChat()->getId(),
                        'text' => 'List of favorite background songs:',
                    ]
                )->then($handler);
            },
            'dump'
        );
    }
};
