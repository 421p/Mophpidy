<?php

use Longman\TelegramBot\Entities\Update;
use Phpidy\Command\Command;
use Phpidy\Telegram\Callback\CallbackStorage;
use Phpidy\Telegram\Callback\StoredCallback;
use function Functional\map;

return new class('/favorites/i') extends Command
{
    function execute(Update $update, array $matches)
    {
        $storage = $this->getContainer()->get(CallbackStorage::class);

        $this->sender->sendMessage(
            [
                'reply_markup' => [
                    'keyboard ' => $this->sender->getKeyboard(),
                    'inline_keyboard' => map(
                        array_keys(
                            $this->getParameter('music_bag')
                        ),
                        function (string $song) use ($storage) {
                            $callback = new StoredCallback($song);

                            $storage->push($callback);

                            return [
                                [
                                    'text' => $song,
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
    }
};