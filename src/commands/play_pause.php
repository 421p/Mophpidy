<?php

use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Update;
use Phpidy\Api\Player;
use Phpidy\Command\Command;

return new class('/(?<operation>play|pause)/i') extends Command
{
    function execute(Update $update, array $matches)
    {
        $player = $this->getContainer()->get(Player::class);

        $operation = strtolower($matches['operation']);
        $player->$operation();
    }
};