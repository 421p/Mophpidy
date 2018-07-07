<?php

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Api\Player;
use Mophpidy\Command\Command;
use Mophpidy\Entity\CallbackContainer;

return new class('/volume\s(?<val>(?:[0-9]+)|up|down)/i') extends Command
{

    function execute(Update $update, array $matches, CallbackContainer $callback = null)
    {
        $chatId = $update->getMessage()->getChat()->getId();

        /** @var Player $player */
        $player = $this->getContainer()->get(Player::class);
        $val = $matches['val'];

        if (is_numeric($val)) {
            $player->setVolume($val)->then(
                function () use ($player, $chatId) {
                    $player->getVolume()->then(
                        function (int $value) use ($chatId) {
                            $this->sender->sendMessageWithDefaultKeyboard(
                                [
                                    'chat_id' => $chatId,
                                    'text' => 'Current volume: '.$value,
                                ]
                            );
                        }
                    );
                }
            );
        } else {
            switch ($val) {
                case 'up':

                    $player->getVolume()->then(
                        function (int $value) use ($chatId, $player) {

                            $value += 10;

                            if ($value > 100) {
                                $value = 100;
                            }

                            $player->setVolume($value)->then(
                                function () use ($value, $chatId) {
                                    $this->sender->sendMessageWithDefaultKeyboard(
                                        [
                                            'chat_id' => $chatId,
                                            'text' => 'Current volume: '.$value,
                                        ]
                                    );
                                }
                            );
                        }
                    );

                    break;
                case 'down':

                    $player->getVolume()->then(
                        function (int $value) use ($chatId, $player) {

                            $value -= 10;

                            if ($value < 0) {
                                $value = 0;
                            }

                            $player->setVolume($value)->then(
                                function () use ($value, $chatId) {
                                    $this->sender->sendMessageWithDefaultKeyboard(
                                        [
                                            'chat_id' => $chatId,
                                            'text' => 'Current volume: '.$value,
                                        ]
                                    );
                                }
                            );
                        }
                    );

                    break;
            }
        }
    }
};