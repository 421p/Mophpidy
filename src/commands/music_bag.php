<?php

use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Update;
use Phpidy\Api\Player;
use Phpidy\Command\Command;

return new class extends Command
{
    /** @var Command[] */
    private $commands;

    /** @var Command|null */
    private $lastMatchedCommand;

    public function __construct()
    {

        $this->commands = $this->populate();
        parent::__construct('');
    }

    public function match(string $text, array &$matches): bool
    {
        foreach ($this->commands as $command) {
            if ($command->match($text, $matches)) {
                $this->lastMatchedCommand = $command;

                return true;
            } else {
                continue;
            }
        }

        $matches = [];

        return false;
    }

    private function populate(): array
    {
        $songs = $this->getParameter('music_bag');

        $commands = [];

        foreach ($songs as $regex => $song) {
            $commands[] = new class($regex, $song) extends Command
            {
                private $song;

                public function __construct(string $regex, array $song)
                {
                    $this->song = $song;
                    parent::__construct(sprintf('/%s/', $regex));
                }

                function execute(Update $update, array $matches)
                {
                    $message = $update->getMessage() ?? $update->getCallbackQuery()->getMessage();

                    $chat_id = $message->getChat()->getId();

                    $player = $this->getContainer()->get(Player::class);

                    $player->playSingleTrack($this->song['album'], $this->song['name'])->then(
                        function () use ($chat_id) {
                            $this->sender->sendMessageWithDefaultKeyboard(
                                [
                                    'chat_id' => $chat_id,
                                    'text' => $this->song['message'],
                                ]
                            );
                        }
                    );

                }
            };
        }

        return $commands;
    }

    function execute(Update $update, array $matches)
    {
        if ($this->lastMatchedCommand !== null) {
            $this->lastMatchedCommand->execute($update, $matches);
        }
        $this->lastMatchedCommand = null;
    }
};