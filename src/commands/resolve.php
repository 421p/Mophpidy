<?php

use function Functional\invoke;
use Longman\TelegramBot\Entities\Update;
use Mophpidy\Api\Player;
use Mophpidy\Behaviour\Browser;
use Mophpidy\Command\Command;
use Mophpidy\Entity\CallbackContainer;
use Mophpidy\Entity\CallbackPayloadItem;
use Mophpidy\Logging\Log;
use Mophpidy\Storage\Storage;
use React\Promise as When;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

return new class('/\/resolve/i') extends Command {
    use Browser;

    public function execute(Update $update, array $matches, CallbackContainer $callback = null)
    {
        try {
            switch ($callback->getType()) {
                case CallbackContainer::TRACKS:
                    $this->handlePlaying($callback, $update)->then(function () use ($callback) {
                        $storage = $this->getContainer()->get(Storage::class);
                        $storage->removeCallback($callback);
                    });
                    break;
                case CallbackContainer::DIRECTORIES:
                    $this->handleBrowsing($callback, $update)->then(function () use ($callback) {
                        $storage = $this->getContainer()->get(Storage::class);
                        $storage->removeCallback($callback);
                    });
                    break;
                default:
                    throw new \RuntimeException('Unknown type of callback.');
            }
        } catch (\Throwable $e) {
            Log::error($e);
        }
    }

    private function handleBrowsing(CallbackContainer $callback, Update $update): PromiseInterface
    {
        $defer = new Deferred();

        /** @var Player $player */
        $player = $this->getContainer()->get(Player::class);

        $payload = $callback->getPayload();

        $index = $callback->getSelectIndex();
        $uri = null !== $index ? $payload[$index]->getUri() : null;

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

        $start = $payload[$index]->getName();

        $uris = invoke($payload, 'getUri');

        $first = $uris[$index];
        unset($uris[$index]);
        array_unshift($uris, $first);

        $player->playList($uris)->then(
            function () use ($update, $callback, $start, $defer) {
                try {
                    $callbackId = $update->getCallbackQuery()->getId();

                    When\all(
                        [
                            $this->sender->answerCallbackQuery($callbackId),
                            $this->sender->deleteMessage($callback->getUserId(), $callback->getMessageId()),
                        ]
                    )->then(
                        \Closure::fromCallable([$defer, 'resolve']),
                        \Closure::fromCallable([$defer, 'reject'])
                    );
                } catch (\Throwable $e) {
                    Log::error($e);
                }
            },
            \Closure::fromCallable([$defer, 'reject'])
        );

        return $defer->promise();
    }
};
