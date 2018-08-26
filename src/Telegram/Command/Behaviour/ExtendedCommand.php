<?php


namespace Mophpidy\Telegram\Command\Behaviour;


use Mophpidy\Command\CommandHolder;
use Mophpidy\Storage\Storage;
use Mophpidy\Telegram\TelegramCommunicator;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait ExtendedCommand
{
    /** @var TelegramCommunicator */
    protected $sender;

    /** @var CommandHolder */
    protected $holder;

    /** @var ContainerInterface */
    protected $container;

    protected function getParameter(string $name)
    {
        return $this->container->getParameter($name);
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->sender = $this->container->get(TelegramCommunicator::class);
        $this->holder = $this->container->get(CommandHolder::class);
    }

    protected function getStorage(): Storage
    {
        return $this->container->get(Storage::class);
    }

    public function execute()
    {
        $this->doExecute();

        return parent::execute();
    }

    abstract protected function doExecute(): void;
}