<?php

use Longman\TelegramBot\Entities\Update;
use Phpidy\Api\Player;
use Phpidy\Command\Command;

return new class('/volume (?<val>[0-9]+)/i') extends Command {

    function execute(Update $message, array $matches)
    {
        $player = $this->getContainer()->get(Player::class);

        $player->setVolume($matches['val']);
    }
};