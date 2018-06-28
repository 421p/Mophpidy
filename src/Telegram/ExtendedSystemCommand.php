<?php

namespace Mophpidy\Telegram;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Telegram;
use Mophpidy\Behaviour\ContainerAccess;
use Mophpidy\Command\CommandHolder;

class ExtendedSystemCommand extends SystemCommand
{
    use ContainerAccess;

    protected $sender;
    protected $holder;
    protected $allowedUsers;

    public function __construct(Telegram $telegram, Update $update = null)
    {
        $this->sender = $this->getContainer()->get(TelegramCommunicator::class);
        $this->holder = $this->getContainer()->get(CommandHolder::class);
        $this->allowedUsers = array_map('trim', explode(',', getenv('ALLOWED_USERS')));

        parent::__construct($telegram, $update);
    }

    protected function isUserAllowed(int $id)
    {
        return in_array($id, $this->allowedUsers);
    }
}