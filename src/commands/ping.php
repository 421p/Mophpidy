<?php

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Command\Command;
use Mophpidy\Entity\CallbackContainer;

return new class('/\/ping/i') extends Command
{
    function execute(Update $update, array $matches, CallbackContainer $callback = null)
    {
        $this->sender->sendMessageWithDefaultKeyboard(
            [
                'chat_id' => $update->getMessage()->getChat()->getId(),
                'text' => 'pong',
            ]
        );
    }
};