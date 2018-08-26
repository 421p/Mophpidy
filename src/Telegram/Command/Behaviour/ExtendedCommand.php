<?php


namespace Mophpidy\Telegram\Command\Behaviour;


use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Telegram;
use Mophpidy\Behaviour\ContainerAccess;
use Mophpidy\Command\CommandHolder;
use Mophpidy\Storage\Storage;
use Mophpidy\Telegram\TelegramCommunicator;

trait ExtendedCommand
{
    use ContainerAccess;

    protected $sender;
    protected $holder;

    public function __construct(Telegram $telegram, Update $update = null)
    {
        $this->sender = $this->getContainer()->get(TelegramCommunicator::class);
        $this->holder = $this->getContainer()->get(CommandHolder::class);

        parent::__construct($telegram, $update);
    }

    protected function getStorage(): Storage
    {
        return $this->getContainer()->get(Storage::class);
    }

    public function execute()
    {
        $this->doExecute();

        return parent::execute();
    }

    abstract protected function doExecute(): void;
}