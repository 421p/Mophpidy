<?php

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Api\Player;
use Mophpidy\Behaviour\Browser;
use Mophpidy\Command\Command;

return new class('/browse/i') extends Command
{
    use Browser;

    function execute(Update $update, array $matches)
    {
        /** @var Player $player */
        $player = $this->getContainer()->get(Player::class);

        $this->browse($update, $player);
    }
};