<?php

namespace Phpidy\Command;

use Longman\TelegramBot\Entities\Update;
use Phpidy\Behaviour\ContainerAccess;
use Phpidy\Telegram\Sender;

abstract class Command
{
    use ContainerAccess;

    private $regex;
    protected $sender;

    public function __construct(string $regex)
    {
        $this->regex = $regex;
        $this->sender = $this->getContainer()->get(Sender::class);
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