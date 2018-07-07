<?php

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Api\Library;
use Mophpidy\Api\Player;
use Mophpidy\Command\Command;
use Mophpidy\Entity\CallbackContainer;
use Mophpidy\Storage\Storage;

return new class('/\/search_(?<command>gmusic|soundcloud)\s(?<query>.+)/') extends Command
{

    function execute(Update $update, array $matches, CallbackContainer $callback = null)
    {
        $message = $update->getMessage();
        $query = $matches['query'];

        $uris = [$matches['command'].':'];

        $player = $this->getContainer()->get(Player::class);

        /** @var Library $lib */
        $lib = $player->getLibrary();

        $lib->search($query, $uris)->then(
            function ($data) use ($message) {
                if (count($data[0]['tracks']) === 0) {
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
                        $storage->getUser($message->getChat()->getId()),
                        $message->getMessageId()
                    );

                    $storage->addCallback($callback);

                    $this->sender->sendMessage(
                        [
                            'reply_markup' => [
                                'inline_keyboard' => $callback->mapInlineKeyboard(),
                            ],
                            'chat_id' => $message->getChat()->getId(),
                            'text' => 'Found:',
                        ]
                    );
                }
            }
        );

    }
};
