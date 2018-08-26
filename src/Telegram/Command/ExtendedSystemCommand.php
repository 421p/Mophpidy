<?php

namespace Mophpidy\Telegram\Command;

use Longman\TelegramBot\Commands\SystemCommand;
use Mophpidy\Telegram\Command\Behaviour\ExtendedCommand;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

abstract class ExtendedSystemCommand extends SystemCommand implements ContainerAwareInterface
{
    use ExtendedCommand;
}
