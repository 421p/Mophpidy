<?php


namespace Mophpidy\Telegram\Command\Behaviour;

use Longman\TelegramBot\Entities\Message;
use Mophpidy\Command\Command;
use Mophpidy\Storage\Storage;
use Mophpidy\Telegram\TelegramCommunicator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @property TelegramCommunicator $sender
 * @method Storage getStorage()
 * @method ContainerInterface getContainer()
 */
trait GenericExecutor
{
    protected function doExecute(): void
    {
        /** @var Message $message */
        $message = $this->getMessage();

        $chat_id = $message->getChat()->getId();

        $this->getStorage()->isUserAllowed($chat_id)->then(function (bool $isAllowed) use ($message, $chat_id) {
            if ($isAllowed) {
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
                        'text' => $this->getContainer()->getParameter('not.allowed'),
                    ]
                );
            }
        });
    }
}