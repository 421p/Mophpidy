<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Request;
use Mophpidy\Entity\User;
use Mophpidy\Storage\Storage;
use Mophpidy\Telegram\ExtendedUserCommand;

class RequestAccessCommand extends ExtendedUserCommand
{
    protected $name = 'request_access';

    /**
     * Execute command.
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        /** @var Storage $player */
        $storage = $this->getContainer()->get(Storage::class);

        $chat = $this->getMessage()->getChat();

        $id = $chat->getId();
        $name = $chat->getFirstName();

        $user = $storage->getUser($id);

        if (null !== $user) {
            $this->sender->sendMessageWithDefaultKeyboard([
                'chat_id' => $id,
                'text' => 'You already have access.',
            ]);

            return Request::emptyResponse();
        }

        /** @var User $admin */
        foreach ($storage->getAdmins() as $admin) {
            $this->sender->sendMessageWithDefaultKeyboard(
                [
                    'chat_id' => $admin->getId(),
                    'parse_mode' => 'Markdown',
                    'text' => sprintf(
                        '
[%1$s](tg://user?id=%2$d) wants to have access to a bot!
Use /allow%2$d to accept request.',
                        $name,
                        $id
                    ),
                ]
            );
        }

        $this->sender->sendMessageWithDefaultKeyboard([
            'chat_id' => $id,
            'text' => 'Access request was successfully sent to administrator.',
        ]);

        return Request::emptyResponse();
    }
}
