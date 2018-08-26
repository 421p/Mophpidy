<?php

namespace Mophpidy\Telegram\Command;

use Longman\TelegramBot\Commands\UserCommand;
use Mophpidy\Telegram\Command\Behaviour\ExtendedCommand;

abstract class ExtendedUserCommand extends UserCommand
{
    use ExtendedCommand;
}
