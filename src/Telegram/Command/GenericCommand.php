<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Mophpidy\Behaviour\GenericExecutor;
use Mophpidy\Telegram\ExtendedSystemCommand;

/**
 * Generic command
 */
class GenericCommand extends ExtendedSystemCommand
{
    use GenericExecutor;

    protected $name = 'generic';
    protected $description = 'Handles generic commands or is executed by default when a command is not found';

    public function execute()
    {
        return $this->executeGeneric();
    }
}
