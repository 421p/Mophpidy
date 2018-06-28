<?php

use Longman\TelegramBot\Entities\Update;
use Mophpidy\Command\Command;

return new class('/\/restart_mopidy/i') extends Command
{
    function execute(Update $update, array $matches)
    {
        exec('sudo systemctl restart mopidy');
    }
};