<?php

use Mophpidy\DI\Injector;
use Mophpidy\Logging\Log;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

Log::initialize();
Injector::initialize();
