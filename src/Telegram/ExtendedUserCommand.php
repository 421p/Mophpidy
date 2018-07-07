<?php

namespace Mophpidy\Telegram;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\Update;
use Mophpidy\Behaviour\ContainerAccess;
use Mophpidy\Command\CommandHolder;
use Mophpidy\Storage\Storage;

abstract class ExtendedUserCommand extends UserCommand
{
    use ContainerAccess;

    protected $sender;
    protected $holder;
    protected $storage;

    public function __construct(Telegram $telegram, Update $update = null)
    {
        $this->sender = $this->getContainer()->get(TelegramCommunicator::class);
        $this->holder = $this->getContainer()->get(CommandHolder::class);
        $this->storage = $this->getContainer()->get(Storage::class);

        parent::__construct($telegram, $update);
    }

    protected function isUserAllowed(int $id)
    {
        return $this->storage->isUserAllowed($id);
    }
}