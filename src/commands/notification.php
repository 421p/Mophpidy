<?php

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Command\Command;
use Mophpidy\Entity\CallbackContainer;
use Mophpidy\Storage\Storage;

return new class('/\/(?<operation>enable|disable)_notifications/i') extends Command {
    public function execute(Update $update, array $matches, CallbackContainer $callback = null)
    {
        $chatId = $update->getMessage()->getChat()->getId();
        /** @var Storage $player */
        $storage = $this->getContainer()->get(Storage::class);

        $operation = strtolower($matches['operation']);

        $method = $operation.'Notifications';

        if ($storage->$method($chatId)) {
            $this->sender->sendMessageWithDefaultKeyboard(
                [
                    'chat_id' => $chatId,
                    'text' => sprintf('Notifications %sd.', $operation),
                ]
            );
        }
    }
};
