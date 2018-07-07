<?php

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Api\Player;
use Mophpidy\Command\Command;
use Mophpidy\Entity\CallbackContainer;

return new class('/(?<operation>play|pause|stop|resume)/i') extends Command
{
    function execute(Update $update, array $matches, CallbackContainer $callback = null)
    {
        /** @var Player $player */
        $player = $this->getContainer()->get(Player::class);

        $operation = strtolower($matches['operation']);

        $player->$operation();
    }
};