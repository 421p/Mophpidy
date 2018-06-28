<?php

namespace Mophpidy\Telegram;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\Update;
use Mophpidy\Behaviour\ContainerAccess;
use Mophpidy\Command\CommandHolder;

abstract class ExtendedUserCommand extends UserCommand
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