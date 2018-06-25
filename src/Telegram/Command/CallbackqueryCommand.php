<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Phpidy\Command\Command;
use Phpidy\Telegram\Callback\CallbackStorage;
use Phpidy\Telegram\ExtendedSystemCommand;

/**
 * Inline query command
 */
class CallbackqueryCommand extends ExtendedSystemCommand
{
    public function execute()
    {
        $data = $this->getUpdate()->getCallbackQuery()->getData();

        $chatId = $this->getUpdate()->getCallbackQuery()->getMessage()->getChat()->getId();
        $messageId = $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId();

        if ($this->isUserAllowed($chatId)) {

            $storage = $this->getContainer()->get(CallbackStorage::class);
            $callback = $storage->get($data);

            if (!$callback) {

                $this->sender->answerCallbackQuery(
                    $this->getUpdate()->getCallbackQuery()->getId(),
                    [
                        'text' => 'Select faster next time. Song will not be played.',
                    ]
                );

                $this->sender->deleteMessage($chatId, $messageId);

                return parent::execute();
            }

            /** @var Command $command */
            foreach ($this->holder as $command) {
                $matches = [];

                if ($command->match($callback->getCommand(), $matches)) {
                    $command->execute($this->getUpdate(), $matches);
                }

                $this->sender->deleteMessage($chatId, $messageId);
            }

        } else {
            $this->sender->sendMessageWithDefaultKeyboard(
                [
                    'chat_id' => $chatId,
                    'text' => 'You are not a member of Priton community.',
                ]
            );
        }

        return parent::execute();
    }
}
