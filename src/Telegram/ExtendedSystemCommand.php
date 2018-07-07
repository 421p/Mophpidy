<?php

namespace Mophpidy\Telegram;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Telegram;
use Mophpidy\Behaviour\ContainerAccess;
use Mophpidy\Command\CommandHolder;
use Mophpidy\Storage\Storage;

class ExtendedSystemCommand extends SystemCommand
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