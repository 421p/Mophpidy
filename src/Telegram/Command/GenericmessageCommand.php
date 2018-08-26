<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Mophpidy\Telegram\Command\Behaviour\GenericExecutor;
use Mophpidy\Telegram\Command\ExtendedSystemCommand;

class GenericmessageCommand extends ExtendedSystemCommand
{
    use GenericExecutor;

    protected $name = 'generic';
    protected $description = 'Handles generic commands or is executed by default when a command is not found';
}
