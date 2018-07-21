<?php

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Api\Player;
use Mophpidy\Behaviour\Browser;
use Mophpidy\Command\Command;
use Mophpidy\Entity\CallbackContainer;
use Mophpidy\Entity\CallbackPayloadItem;
use Mophpidy\Storage\Storage;
use React\Promise as When;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

return new class('/\/resolve (?<id>.+)/i') extends Command {
    use Browser;

    public function execute(Update $update, array $matches, CallbackContainer $callback = null)
    {
        switch ($callback->getType()) {
            case CallbackContainer::TRACKS:
                $this->handlePlaying($callback, $update)->then(function () use ($callback) {
                    $storage = $this->getContainer()->get(Storage::class);
                    $storage->removeCallback($callback->getRoot());
                });
                break;
            case CallbackContainer::DIRECTORIES:
                $this->handleBrowsing($callback, $update);
                break;
            default:
                throw new \RuntimeException('Unknown type of callback.');
        }
    }

    private function handleBrowsing(CallbackContainer $callback, Update $update): PromiseInterface
    {
        $defer = new Deferred();

        /** @var Player $player */
        $player = $this->getContainer()->get(Player::class);

        $payload = $callback->getPayload();

        $index = $callback->getSelectIndex();
        $uri = null !== $index ? $payload->get($index)->getUri() : null;

        $this->sender->answerCallbackQuery($update->getCallbackQuery()->getId())->then(
            function () use ($update, $player, $uri, $defer, $callback) {
                $chatId = $update->getCallbackQuery()->getMessage()->getChat()->getId();
                $messageId = $update->getCallbackQuery()->getMessage()->getMessageId();

                try {
                    $this->browse($update, $player, $chatId, $messageId, $uri, $callback);
                    $defer->resolve();
                } catch (\Throwable $e) {
                    $defer->reject($e);
                }
            },
            \Closure::fromCallable([$defer, 'reject'])
        );

        return $defer->promise();
    }

    private function handlePlaying(CallbackContainer $callback, Update $update): PromiseInterface
    {
        $defer = new Deferred();

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
            function () use ($update, $callback, $start, $defer) {
                $callbackId = $update->getCallbackQuery()->getId();

                When\all(
                    [
                        $this->sender->answerCallbackQuery($callbackId),
                        $this->sender->deleteMessage($callback->getUser()->getId(), $callback->getMessageId()),
                    ]
                )->then(
                    \Closure::fromCallable([$defer, 'resolve']),
                    \Closure::fromCallable([$defer, 'reject'])
                );
            },
            \Closure::fromCallable([$defer, 'reject'])
        );

        return $defer->promise();
    }
};
