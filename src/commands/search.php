<?php

use Longman\TelegramBot\Entities\Update;
use Phpidy\Api\Library;
use Phpidy\Api\Player;
use Phpidy\Command\Command;
use Phpidy\Telegram\Callback\CallbackStorage;
use Phpidy\Telegram\Callback\StoredCallback;
use function Functional\map;

return new class('/\/(?<command>search|soundcloud)\s(?<query>.+)/') extends Command
{

    function execute(Update $update, array $matches)
    {
        $message = $update->getMessage();
        $query = $matches['query'];

        $uris = $matches['command'] === 'soundcloud' ? ['soundcloud:'] : ['gmusic:'];

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
                    $storage = $this->getContainer()->get(CallbackStorage::class);

                    $this->sender->sendMessage(
                        [
                            'reply_markup' => [
                                'keyboard ' => $this->sender->getKeyboard(),
                                'inline_keyboard' => map(
                                    $data[0]['tracks'],
                                    function (array $track) use ($storage) {
                                        $name = sprintf('%s - %s', $track['artists'][0]['name'], $track['name']);

                                        $callback = new StoredCallback(
                                            sprintf('/playuri %s', $track['uri']),
                                            ['name' => $name]
                                        );

                                        $storage->push($callback);

                                        return [
                                            [
                                                'text' => $name,
                                                'callback_data' => $callback->getId()->toString(),
                                            ],
                                        ];
                                    }
                                )
                                ,
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
