<?php

namespace Mophpidy\Command;

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Behaviour\ContainerAccess;
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
        return preg_match($this->regex, $text, $matches) === 1;
    }

    abstract function execute(Update $update, array $matches);

    public function getRegex(): string
    {
        return $this->regex;
    }
}