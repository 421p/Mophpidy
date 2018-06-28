<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Mophpidy\Telegram\ExtendedSystemCommand;

class StartCommand extends ExtendedSystemCommand
{
    protected $name = 'start';
    protected $description = 'Start command';
    protected $usage = '/start';

    public function execute()
    {
        $message = $this->getMessage();

        $chat_id = $message->getChat()->getId();

        $data = [
            'chat_id' => $chat_id,
            'text' => 'Welcome to Priton',
        ];

        $this->sender->sendMessageWithDefaultKeyboard($data);

        return parent::execute();
    }
}