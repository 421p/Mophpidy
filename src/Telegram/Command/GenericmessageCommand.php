<?php

namespace Mophpidy\Telegram\Command;

use Mophpidy\Telegram\Command\Behaviour\GenericExecutor;

class GenericmessageCommand extends ExtendedSystemCommand
{
    use GenericExecutor;

    protected $name = 'generic';
    protected $description = 'Handles generic commands or is executed by default when a command is not found';
}
