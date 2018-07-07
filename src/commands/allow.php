<?php

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Command\Command;
use Mophpidy\Entity\CallbackContainer;
use Mophpidy\Storage\Storage;

return new class('/\/allow(?<id>\d+)/i') extends Command
{
    function execute(Update $update, array $matches, CallbackContainer $callback = null)
    {
        /** @var Storage $storage */
        $storage = $this->getContainer()->get(Storage::class);
        $id = strtolower($matches['id']);

        $adminId = $update->getMessage()->getChat()->getId();
        $adminName = $update->getMessage()->getChat()->getFirstName();

        $admin = $storage->getUser($adminId);

        if (!$admin->isAdmin()) {
            $this->sender->sendMessageWithDefaultKeyboard(
                [
                    'chat_id' => $adminId,
                    'text' => 'Only admin can accept users.',
                ]
            );

            return;
        }

        $user = $storage->getUser($id);

        if ($user !== null) {
            $this->sender->sendMessageWithDefaultKeyboard(
                [
                    'chat_id' => $adminId,
                    'text' => 'User is already accepted.',
                ]
            );

            return;
        }

        $storage->addDefaultUser($id);

        foreach ($storage->getNotificationSubscribers() as $user) {
            $this->sender->sendMessageWithDefaultKeyboard(
                [
                    'chat_id' => $user->getId(),
                    'parse_mode' => 'Markdown',
                    'text' => sprintf(
                        '[%s](tg://user?id=%d) just accepted a new [member](tg://user?id=%d).',
                        $adminName,
                        $adminId,
                        $id
                    ),
                ]
            );
        }

        $this->sender->sendMessageWithDefaultKeyboard(
            [
                'chat_id' => $id,
                'text' => '
You were accepted!
Feel free to interact with bot now.

Do not forget to use /enable_notifications if you want to receive information about switching tracks and other interesting events.
                ',
            ]
        );
    }
};