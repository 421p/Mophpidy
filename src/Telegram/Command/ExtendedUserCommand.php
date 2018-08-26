<?php

namespace Mophpidy\Telegram\Command;

use Longman\TelegramBot\Commands\UserCommand;
use Mophpidy\Telegram\Command\Behaviour\ExtendedCommand;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

abstract class ExtendedUserCommand extends UserCommand implements ContainerAwareInterface
{
    use ExtendedCommand;
}
