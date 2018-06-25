<?php

namespace Phpidy\Telegram;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Telegram;
use Phpidy\Behaviour\ContainerAccess;
use Phpidy\Command\CommandHolder;

class ExtendedSystemCommand extends SystemCommand
{
    use ContainerAccess;

    protected $sender;
    protected $holder;
    protected $allowedUsers;

    public function __construct(Telegram $telegram, Update $update = null)
    {
        $this->sender = $this->getContainer()->get(Sender::class);
        $this->holder = $this->getContainer()->get(CommandHolder::class);
        $this->allowedUsers = json_decode(getenv('ALLOWED_USERS'), true);

        parent::__construct($telegram, $update);
    }

    protected function isUserAllowed(int $id)
    {
        return in_array($id, $this->allowedUsers);
    }
}