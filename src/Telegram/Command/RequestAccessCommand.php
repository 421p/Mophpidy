<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Mophpidy\Entity\User;
use Mophpidy\Storage\Storage;
use Mophpidy\Telegram\Command\ExtendedUserCommand;

class RequestAccessCommand extends ExtendedUserCommand
{
    protected $name = 'request_access';

    protected function doExecute(): void
    {
        /** @var Storage $player */
        $storage = $this->getContainer()->get(Storage::class);

        $chat = $this->getMessage()->getChat();

        $storage->getUser($chat->getId())->then(
            function (User $user) use ($storage, $chat) {
                $id   = $chat->getId();
                $name = $chat->getFirstName();

                if (null !== $user) {
                    $this->sender->sendMessageWithDefaultKeyboard(
                        [
                            'chat_id' => $id,
                            'text'    => 'You already have access.',
                        ]
                    );

                    return;
                }

                $storage->forEachAdmin(
                    function (User $admin) use ($name, $id) {
                        $this->sender->sendMessageWithDefaultKeyboard(
                            [
                                'chat_id'    => $admin->getId(),
                                'parse_mode' => 'Markdown',
                                'text'       => sprintf(
                                    '
[%1$s](tg://user?id=%2$d) wants to have access to a bot!
Use /allow%2$d to accept request.',
                                    $name,
                                    $id
                                ),
                            ]
                        );
                    }
                );

                $this->sender->sendMessageWithDefaultKeyboard(
                    [
                        'chat_id' => $id,
                        'text'    => 'Access request was successfully sent to administrator.',
                    ]
                );
            }
        );
    }
}
