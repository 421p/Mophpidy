<?php

use Longman\TelegramBot\Entities\Update;
use Phpidy\Api\Player;
use Phpidy\Command\Command;
use Phpidy\Telegram\Callback\CallbackStorage;
use Phpidy\Telegram\Callback\StoredCallback;
use function Functional\map;

return new class('/promoted/i') extends Command
{
    function execute(Update $update, array $matches)
    {
        $storage = $this->getContainer()->get(CallbackStorage::class);

        /** @var Player $player */
        $player = $this->getContainer()->get(Player::class);

        $player->getLibrary()->getFavorites()->then(
            function (array $data) use ($storage, $update) {
                $this->sender->sendMessage(
                    [
                        'reply_markup' => [
                            'keyboard ' => $this->sender->getKeyboard(),
                            'inline_keyboard' => map(
                                $data,
                                function (array $song) use ($storage) {
                                    $callback = new StoredCallback(sprintf('/playuri %s', $song['uri']), [
                                        'name' => $song['name']
                                    ]);

                                    $storage->push($callback);

                                    return [
                                        [
                                            'text' => $song['name'],
                                            'callback_data' => $callback->getId()->toString(),
                                        ],
                                    ];
                                }
                            )
                            ,
                        ],
                        'chat_id' => $update->getMessage()->getChat()->getId(),
                        'text' => 'List of favorite background songs:',
                    ]
                );
            }, 'dump'
        );
    }
};