<?php

namespace Mophpidy\Behaviour;

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Api\Player;
use Mophpidy\Entity\CallbackContainer;
use Mophpidy\Logging\Log;
use Mophpidy\Storage\Storage;
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

                try {

                    $type = $data[0]['type'] === 'directory' ? CallbackContainer::DIRECTORIES : CallbackContainer::TRACKS;

                    /** @var Storage $storage */
                    $storage = $this->getContainer()->get(Storage::class);

                    $callback = CallbackContainer::pack($data, $type, $storage->getUser($chatId));

                    $storage->addCallback($callback);

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

                } catch (\Throwable $e) {
                    dump($e);
                }
            },
            function (\Throwable $e) {
                Log::error($e->__toString());
            }
        );
    }
}