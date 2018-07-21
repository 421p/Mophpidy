<?php

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Api\Library;
use Mophpidy\Api\Player;
use Mophpidy\Command\Command;
use Mophpidy\Entity\CallbackContainer;
use Mophpidy\Storage\Storage;

return new class('/\/search_(?<command>gmusic|soundcloud)\s(?<query>.+)/') extends Command {
    public function execute(Update $update, array $matches, CallbackContainer $callback = null)
    {
        $message = $update->getMessage();
        $query = $matches['query'];

        $uris = [$matches['command'].':'];

        $player = $this->getContainer()->get(Player::class);

        /** @var Library $lib */
        $lib = $player->getLibrary();

        $lib->search($query, $uris)->then(
            function ($data) use ($message) {
                if (0 === count($data[0]['tracks'])) {
                    $this->sender->sendMessageWithDefaultKeyboard(
                        [
                            'chat_id' => $message->getChat()->getId(),
                            'text' => 'No tracks found.',
                        ]
                    );
                } else {
                    $storage = $this->getContainer()->get(Storage::class);

                    $callback = CallbackContainer::pack(
                        $data[0]['tracks'],
                        CallbackContainer::TRACKS,
                        $storage->getUser($message->getChat()->getId())
                    );

                    $handler = function (array $data) use ($storage, $callback) {
                        $callback->setMessageId($data['message_id']);
                        $storage->addCallback($callback);
                    };

                    $this->sender->sendMessage(
                        [
                            'reply_markup' => [
                                'inline_keyboard' => $callback->mapInlineKeyboard(),
                            ],
                            'chat_id' => $message->getChat()->getId(),
                            'text' => 'Found:',
                        ]
                    )->then($handler);
                }
            }
        );
    }
};
