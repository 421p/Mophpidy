<?php

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Api\Player;
use Mophpidy\Behaviour\Browser;
use Mophpidy\Command\Command;
use Mophpidy\Entity\CallbackContainer;

return new class('/browse/i') extends Command
{
    use Browser;

    function execute(Update $update, array $matches, CallbackContainer $callback = null)
    {
        /** @var Player $player */
        $player = $this->getContainer()->get(Player::class);

        $this->browse(
            $update,
            $player,
            $update->getMessage()->getChat()->getId(),
            $update->getMessage()->getMessageId()
        );
    }
};