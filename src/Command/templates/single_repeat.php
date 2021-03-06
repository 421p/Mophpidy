<?php

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Api\Player;
use Mophpidy\Command\Command;
use Mophpidy\Entity\CallbackContainer;

return new class('/(?<operation>single|repeat)/i') extends Command {
    public function execute(Update $update, array $matches, CallbackContainer $callback = null)
    {
        $chatId = $update->getMessage()->getChat()->getId();

        $player = $this->getContainer()->get(Player::class);
        $trackList = $player->getTrackList();

        $option = ucfirst(strtolower($matches['operation']));

        $method = sprintf('get%s', $option);

        $trackList->$method()->then(
            function (bool $value) use ($option, $trackList, $chatId) {
                $method = sprintf('set%s', $option);

                $trackList->$method(!$value)->then(
                    function () use ($option, $value, $chatId) {
                        $text = sprintf('%s mode %s.', $option, !$value ? 'enabled' : 'disabled');

                        $this->sender->sendMessageWithDefaultKeyboard(
                            [
                                'chat_id' => $chatId,
                                'text' => $text,
                            ]
                        );
                    }
                );
            }
        );
    }
};
