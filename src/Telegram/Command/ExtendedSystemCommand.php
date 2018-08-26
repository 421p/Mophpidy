<?php

namespace Mophpidy\Telegram\Command;

use Longman\TelegramBot\Commands\SystemCommand;
use Mophpidy\Telegram\Command\Behaviour\ExtendedCommand;

abstract class ExtendedSystemCommand extends SystemCommand
{
    use ExtendedCommand;
}
