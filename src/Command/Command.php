<?php

namespace Mophpidy\Command;

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Behaviour\ContainerAccess;
use Mophpidy\Entity\CallbackContainer;
use Mophpidy\Telegram\TelegramCommunicator;

abstract class Command
{
    use ContainerAccess;

    protected $sender;
    private $regex;

    public function __construct(string $regex)
    {
        $this->regex = $regex;
        $this->sender = $this->getContainer()->get(TelegramCommunicator::class);
    }

    public function match(string $text, array &$matches): bool
    {
        return 1 === preg_match($this->regex, $text, $matches);
    }

    abstract public function execute(Update $update, array $matches, CallbackContainer $callback = null);

    public function getRegex(): string
    {
        return $this->regex;
    }
}
