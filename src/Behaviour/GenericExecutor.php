<?php

namespace Phpidy\Behaviour;

use Longman\TelegramBot\Entities\Message;
use Phpidy\Command\Command;
use Phpidy\Telegram\Sender;

/** @property Sender $sender */
trait GenericExecutor
{
    protected function executeGeneric()
    {
        /** @var Message $message */
        $message = $this->getMessage();

        $chat_id = $message->getChat()->getId();

        if ($this->isUserAllowed($chat_id)) {
            /** @var Command $command */
            foreach ($this->holder as $command) {
                $matches = [];

                if ($command->match(trim($message->getText()), $matches)) {
                    $command->execute($this->getUpdate(), $matches);
                }
            }
        } else {
            $this->sender->sendMessageWithDefaultKeyboard(
                [
                    'chat_id' => $chat_id,
                    'text' => 'You are not a member of Priton community.',
                ]
            );
        }

        return parent::execute();
    }
}