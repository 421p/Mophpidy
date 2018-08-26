<?php

namespace Mophpidy\Command;

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Entity\CallbackContainer;
use Mophpidy\Telegram\TelegramCommunicator;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class Command implements ContainerAwareInterface
{
    /** @var TelegramCommunicator */
    protected $sender;
    protected $container;
    private $regex;

    public function __construct(string $regex)
    {
        $this->regex = $regex;
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

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param TelegramCommunicator $sender
     */
    public function setSender(TelegramCommunicator $sender): void
    {
        $this->sender = $sender;
    }
}
