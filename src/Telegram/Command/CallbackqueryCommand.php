<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Mophpidy\Command\Command;
use Mophpidy\Entity\CallbackContainer;
use Mophpidy\Telegram\ExtendedSystemCommand;

/**
 * Inline query command.
 */
class CallbackqueryCommand extends ExtendedSystemCommand
{
    /**
     * @return \Longman\TelegramBot\Entities\ServerResponse
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function execute()
    {
        [$id, $index] = explode(':', $this->getUpdate()->getCallbackQuery()->getData());

        $chatId = $this->getUpdate()->getCallbackQuery()->getMessage()->getChat()->getId();
        $messageId = $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId();

        $storage = $this->getStorage();

        if ($storage->isUserAllowed($chatId)) {
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

            switch (intval($index)) {
                case CallbackContainer::DELETE:
                    $this->sender->answerCallbackQuery($this->getUpdate()->getCallbackQuery()->getId());

                    $this->sender->deleteMessage($chatId, $messageId);
                    $storage->removeCallback($callback->getRoot());

                    return parent::execute();

                case CallbackContainer::BACKWARD:
                    $callback = $callback->getParent();
                    break;
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
