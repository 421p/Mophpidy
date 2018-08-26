<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Mophpidy\Command\Command;
use Mophpidy\Entity\CallbackContainer;
use Mophpidy\Telegram\Command\ExtendedSystemCommand;

/**
 * Inline query command.
 */
class CallbackqueryCommand extends ExtendedSystemCommand
{
    protected function doExecute(): void
    {
        [$id, $index] = explode(':', $this->getUpdate()->getCallbackQuery()->getData());

        $chatId    = $this->getUpdate()->getCallbackQuery()->getMessage()->getChat()->getId();
        $messageId = $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId();

        $storage = $this->getStorage();

        $storage->isUserAllowed($chatId)->then(
            function (bool $isAllowed) use ($storage, $chatId, $messageId, $id, $index) {
                if ($isAllowed) {
                    $storage->getCallback($id)->then(
                        function (?CallbackContainer $callback) use ($storage, $chatId, $messageId, $id, $index) {
                            if (!$callback) {
                                $this->sender->answerCallbackQuery(
                                    $this->getUpdate()->getCallbackQuery()->getId(),
                                    [
                                        'text' => 'Select faster next time.',
                                    ]
                                );

                                $this->sender->deleteMessage($chatId, $messageId);

                                return;
                            }

                            switch (intval($index)) {
                                case CallbackContainer::DELETE:
                                    $this->sender->answerCallbackQuery($this->getUpdate()->getCallbackQuery()->getId());

                                    $this->sender->deleteMessage($chatId, $messageId);
                                    $storage->removeCallback($callback);

                                    return;
                                default:
                                    $callback->setSelectIndex($index);
                            }

                            /** @var Command $command */
                            foreach ($this->holder as $command) {
                                $matches = [];

                                if ($command->match($callback->getCommand(), $matches)) {
                                    $command->execute($this->getUpdate(), $matches, $callback);
                                }
                            }
                        }
                    );

                } else {
                    $this->sender->sendMessageWithDefaultKeyboard(
                        [
                            'chat_id' => $chatId,
                            'text'    => $this->getContainer()->getParameter('not.allowed'),
                        ]
                    );
                }
            }
        );
    }
}
