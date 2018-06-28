<?php

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Command\Command;

return new class('/\/ping/i') extends Command
{
    function execute(Update $update, array $matches)
    {
        $this->sender->sendMessageWithDefaultKeyboard(
            [
                'chat_id' => $update->getMessage()->getChat()->getId(),
                'text' => 'pong',
            ]
        );
    }
};