<?php

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Api\Player;
use Mophpidy\Behaviour\Browser;
use Mophpidy\Command\Command;
use Mophpidy\Telegram\Callback\CallbackContainer;
use Mophpidy\Telegram\Callback\CallbackStorage;
use function Functional\map;

return new class('/\/resolve (?<id>.+)/i') extends Command
{
    use Browser;

    function execute(Update $update, array $matches)
    {
        $storage = $this->getContainer()->get(CallbackStorage::class);

        /** @var CallbackContainer $callback */
        $callback = $storage->remove($matches['id']);

        switch ($callback->getType()) {
            case CallbackContainer::TRACKS:
                $this->handlePlaying($callback, $update);
                break;
            case CallbackContainer::DIRECTORIES:
                $this->handleBrowsing($callback, $update);
                break;
        }
    }

    private function handleBrowsing(CallbackContainer $callback, Update $update)
    {
        /** @var Player $player */
        $player = $this->getContainer()->get(Player::class);

        $payload = $callback->getPayload();

        $index = $payload['index'];
        $uri = $payload['dirs'][$index]['uri'];

        $this->sender->answerCallbackQuery($update->getCallbackQuery()->getId())->then(
            function () use ($update, $player, $uri) {

                $chatId = $update->getCallbackQuery()->getMessage()->getChat()->getId();
                $messageId = $update->getCallbackQuery()->getMessage()->getMessageId();

                $this->browse($update, $player, $uri, $chatId, $messageId);
            }
        );
    }

    private function handlePlaying(CallbackContainer $callback, Update $update)
    {
        /** @var Player $player */
        $player = $this->getContainer()->get(Player::class);

        $payload = $callback->getPayload();

        $index = $payload['index'];
        $songs = $payload['songs'];

        $start = $songs[$index]['name'];

        $uris = map(
            $songs,
            function (array $song) {
                return $song['uri'];
            }
        );

        $first = $uris[$index];
        unset($uris[$index]);
        array_unshift($uris, $first);

        $player->playList($uris)->then(
            function () use ($update, $callback, $start) {
                $callbackId = $update->getCallbackQuery()->getId();

                $this->sender->answerCallbackQuery($callbackId);

                $this->sender->sendMessageWithDefaultKeyboard(
                    [
                        'chat_id' => $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                        'text' => 'Playing list, starting from '.$start,
                    ]
                );
            }
        );
    }
};