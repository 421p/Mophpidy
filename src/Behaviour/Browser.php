<?php

namespace Mophpidy\Behaviour;

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Api\Player;
use Mophpidy\Telegram\Callback\CallbackContainer;
use Mophpidy\Telegram\Callback\CallbackStorage;
use Mophpidy\Telegram\TelegramCommunicator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @method ContainerInterface getContainer
 * @property TelegramCommunicator $sender
 */
trait Browser
{
    protected function browse(Update $update, Player $player, $uri = null, $chatId = null, $messageId = null)
    {
        $library = $player->getLibrary();

        $inProgress = $uri !== null;

        $library->browse($uri)->then(
            function (array $data) use ($update, $inProgress, $chatId, $messageId) {

                $type = current($data)['type'];

                $callback = $type === 'directory' ? CallbackContainer::packDirs($data) : CallbackContainer::packTracks($data);

                /** @var CallbackStorage $storage */
                $storage = $this->getContainer()->get(CallbackStorage::class);

                $storage->push($callback);

                $markup = [
                    'inline_keyboard' => $callback->mapInlineKeyboard(),
                ];

                if ($inProgress) {
                    $this->sender->editMessageReplyMarkup($chatId, $messageId, $markup);
                } else {
                    $this->sender->sendMessage(
                        [
                            'reply_markup' => $markup,
                            'chat_id' => $update->getMessage()->getChat()->getId(),
                            'text' => 'Directories:',
                        ]
                    );
                }
            }
        );
    }
}