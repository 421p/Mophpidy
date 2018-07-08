<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Mophpidy\Command\Command;
use Mophpidy\Storage\Storage;
use Mophpidy\Telegram\ExtendedSystemCommand;

/**
 * Inline query command
 */
class CallbackqueryCommand extends ExtendedSystemCommand
{
    /**
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function execute()
    {
        [$id, $index] = explode(':', $this->getUpdate()->getCallbackQuery()->getData());

        $chatId = $this->getUpdate()->getCallbackQuery()->getMessage()->getChat()->getId();
        $messageId = $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId();

        if ($this->isUserAllowed($chatId)) {

            $storage = $this->getContainer()->get(Storage::class);
            $callback = $storage->getCallback($id);

            if (!$callback) {

                $this->sender->answerCallbackQuery(
                    $this->getUpdate()->getCallbackQuery()->getId(),
                    [
                        'text' => 'Select faster next time.',
                    ]
                );

                $this->sender->deleteMessage($chatId, $messageId);

                return parent::execute();
            }

            if (intval($index) === -1) {
                $this->sender->answerCallbackQuery($this->getUpdate()->getCallbackQuery()->getId());

                $this->sender->deleteMessage($chatId, $messageId);
                $storage->removeCallback($callback);

                return parent::execute();
            }

            $callback->setSelectIndex($index);

            /** @var Command $command */
            foreach ($this->holder as $command) {
                $matches = [];

                if ($command->match($callback->getCommand(), $matches)) {
                    $command->execute($this->getUpdate(), $matches, $callback);
                }
            }

        } else {
            $this->sender->sendMessageWithDefaultKeyboard(
                [
                    'chat_id' => $chatId,
                    'text' => $this->getContainer()->getParameter('not.allowed'),
                ]
            );
        }

        return parent::execute();
    }
}
