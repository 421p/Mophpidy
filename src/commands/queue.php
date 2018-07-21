<?php

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Api\Player;
use Mophpidy\Command\Command;
use Mophpidy\Entity\CallbackContainer;
use React\Promise as When;

return new class('/queue/i') extends Command {
    public function execute(Update $update, array $matches, CallbackContainer $callback = null)
    {
        /** @var Player $player */
        $player = $this->getContainer()->get(Player::class);

        $chatId = $update->getMessage()->getChat()->getId();

        When\all(
            [
                $player->getQueue(),
                $player->getCurrentTrack(),
            ]
        )->then(
            function (array $data) use ($chatId) {
                [$queue, $currentTrack] = $data;

                $currentTrackName = $this->makeName($currentTrack);

                $stream = fopen('php://memory', 'rw');

                foreach ($queue as $i => $track) {
                    $trackId = $i + 1;
                    $name = $track['name'];

                    if (array_key_exists('artists', $track)) {
                        $artist = current($track['artists'])['name'];

                        $name = sprintf('%s - %s', $artist, $name);
                    }

                    $pattern = $name === $currentTrackName ? '<b>%d. %s</b>' : '%d. %s';

                    fprintf($stream, $pattern, $trackId, $name);
                    fwrite($stream, PHP_EOL);
                }

                rewind($stream);

                $this->sender->sendMessageWithDefaultKeyboard(
                    [
                        'chat_id' => $chatId,
                        'parse_mode' => 'HTML',
                        'text' => stream_get_contents($stream),
                    ]
                );
            }
        );
    }

    private function makeName(array $track): string
    {
        $name = $track['name'];

        if (array_key_exists('artists', $track)) {
            $artist = current($track['artists'])['name'];

            $name = sprintf('%s - %s', $artist, $name);
        }

        return $name;
    }
};
