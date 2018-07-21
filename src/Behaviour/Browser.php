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
 *
 * @property TelegramCommunicator $sender
 */
trait Browser
{
    protected function browse(
        Update $update,
        Player $player,
        $chatId,
        $messageId,
        $uri = null,
        CallbackContainer $parentCallback = null
    ) {
        $library = $player->getLibrary();

        $inProgress = null !== $uri;

        $library->browse($uri)->then(
            function (array $data) use ($update, $inProgress, $chatId, $messageId, $parentCallback) {
                try {
                    $type = 'directory' === $data[0]['type'] ? CallbackContainer::DIRECTORIES : CallbackContainer::TRACKS;

                    /**
                     * Unique directories: workaround for bug with gmusic directories.
                     */
                    $processed = [];

                    for ($names = [], $i = 0, $limit = count($data); $i < $limit; ++$i) {
                        $item = $data[$i];
                        $name = $item['name'];

                        if (!in_array($name, $names)) {
                            $names[] = $name;

                            $item['name'] = preg_replace('/^(\d+\s\-\s,)/', '', $name);

                            $processed[] = $item;
                        }
                    }

                    /** @var Storage $storage */
                    $storage = $this->getContainer()->get(Storage::class);

                    if (null !== $parentCallback && $parentCallback->hasChildren()) {
                        $callback = $parentCallback;
                        foreach ($callback->getChildren() as $child) {
                            $storage->removeCallback($child);
                        }
                    } else {
                        $callback = CallbackContainer::pack(
                            $processed,
                            $type,
                            $storage->getUser($chatId)
                        );

                        if (null !== $parentCallback) {
                            $parentCallback->addChild($callback);
                        }
                    }

                    $handler = function (array $data) use ($storage, $callback) {
                        $callback->setMessageId($data['message_id']);
                        $storage->addCallback($callback);
                    };

                    $markup = [
                        'inline_keyboard' => $callback->mapInlineKeyboard(),
                    ];

                    if ($inProgress) {
                        $this->sender->editMessageReplyMarkup($chatId, $messageId, $markup)->then($handler);
                    } else {
                        $this->sender->sendMessage(
                            [
                                'reply_markup' => $markup,
                                'chat_id' => $update->getMessage()->getChat()->getId(),
                                'text' => 'Directories:',
                            ]
                        )->then($handler);
                    }
                } catch (\Throwable $e) {
                    Log::error($e);
                }
            },
            function (\Throwable $e) {
                Log::error($e);
            }
        );
    }
}
