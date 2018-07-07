<?php

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Api\Player;
use Mophpidy\Behaviour\Browser;
use Mophpidy\Command\Command;
use Mophpidy\Entity\CallbackContainer;
use Mophpidy\Entity\CallbackPayloadItem;
use Mophpidy\Storage\Storage;

return new class('/\/resolve (?<id>.+)/i') extends Command
{
    use Browser;

    function execute(Update $update, array $matches, CallbackContainer $callback = null)
    {
        $storage = $this->getContainer()->get(Storage::class);

        switch ($callback->getType()) {
            case CallbackContainer::TRACKS:
                $this->handlePlaying($callback, $update);
                break;
            case CallbackContainer::DIRECTORIES:
                $this->handleBrowsing($callback, $update);
                break;
        }

        $storage->removeCallback($callback);
    }

    private function handleBrowsing(CallbackContainer $callback, Update $update)
    {
        /** @var Player $player */
        $player = $this->getContainer()->get(Player::class);

        $payload = $callback->getPayload();

        $index = $callback->getSelectIndex();
        $uri = $payload->get($index)->getUri();

        $this->sender->answerCallbackQuery($update->getCallbackQuery()->getId())->then(
            function () use ($update, $player, $uri) {

                $chatId = $update->getCallbackQuery()->getMessage()->getChat()->getId();
                $messageId = $update->getCallbackQuery()->getMessage()->getMessageId();

                $this->browse($update, $player, $chatId, $messageId, $uri);
            }
        );
    }

    private function handlePlaying(CallbackContainer $callback, Update $update)
    {
        /** @var Player $player */
        $player = $this->getContainer()->get(Player::class);

        $payload = $callback->getPayload();

        $index = $callback->getSelectIndex();

        $start = $payload->get($index)->getName();

        $uris = $payload->map(
            function (CallbackPayloadItem $item) {
                return $item->getUri();
            }
        )->toArray();

        $first = $uris[$index];
        unset($uris[$index]);
        array_unshift($uris, $first);

        $player->playList($uris)->then(
            function () use ($update, $callback, $start) {
                $callbackId = $update->getCallbackQuery()->getId();

                $this->sender->answerCallbackQuery($callbackId);
            }
        );
    }
};