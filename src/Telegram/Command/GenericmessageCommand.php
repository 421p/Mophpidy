<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Phpidy\Behaviour\GenericExecutor;
use Phpidy\Telegram\ExtendedSystemCommand;

class GenericmessageCommand extends ExtendedSystemCommand
{
    use GenericExecutor;

    protected $name = 'generic';
    protected $description = 'Handles generic commands or is executed by default when a command is not found';

    public function execute()
    {
        return $this->executeGeneric();
    }
}
