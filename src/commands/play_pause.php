<?php

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Api\Player;
use Mophpidy\Command\Command;

return new class('/(?<operation>play|pause|stop|resume)/i') extends Command
{
    function execute(Update $update, array $matches)
    {
        $chatId = $update->getMessage()->getChat()->getId();

        /** @var Player $player */
        $player = $this->getContainer()->get(Player::class);

        $operation = strtolower($matches['operation']);

        $player->listenMopidyEventOnce('playback_state_changed', function ($data) use ($chatId) {
            $this->sender->sendMessageWithDefaultKeyboard(
                [
                    'chat_id' => $chatId,
                    'text' => sprintf(
                        'Stage changed from %s to %s',
                        $data['old_state'],
                        $data['new_state']
                    ),
                ]
            );
        });

        $player->$operation();
    }
};