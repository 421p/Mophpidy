<?php

namespace Mophpidy\Telegram\Command;

class StartCommand extends ExtendedSystemCommand
{
    protected $name = 'start';
    protected $description = 'Start command';
    protected $usage = '/start';

    private $startMessage = '
Welcome to <b>Mophpidy</b> 

Some useful commands:

    /enable_notifications - enables notifications about some useful events like a songs switching
    /disable_notifications - disables notifications
    /search_gmusic - searches for track on google music
    /search_soundcloud - searches for track on soundcloud
    /volume - set a custom value for volume [0-100]
    /requestaccess - send a request to be allowed to use bot
    ';

    protected function doExecute(): void
    {
        $message = $this->getMessage();

        $chat_id = $message->getChat()->getId();

        $data = [
            'chat_id' => $chat_id,
            'parse_mode' => 'HTML',
            'text' => $this->startMessage,
        ];

        $this->sender->sendMessageWithDefaultKeyboard($data)->then(null, 'dump');
    }
}
